<?php
/**
 * api/fleet.php
 *
 * GET  /api/fleet.php             → List our fleet, each row enriched with CarAPI car specs
 * GET  /api/fleet.php?proxy=makes → Proxy: list all car makes from CarAPI
 * GET  /api/fleet.php?proxy=models&make_id=57&year=2022 → Proxy: list models
 * GET  /api/fleet.php?proxy=years → Proxy: list available years from CarAPI
 * POST /api/fleet.php             → Add a car to our fleet (body: car_api_trim_id, car_api_year, car_label, plate, rent_cost_per_day, status)
 * DELETE /api/fleet.php           → Remove a car from our fleet (body: id)
 */
ini_set('display_errors', 0);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/carapi.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// ─────────────────────────────────────────────────────────────────────────────
// CarAPI proxy routes (GET only) – forward requests to CarAPI server-side
// so we never expose credentials to the browser
// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'GET' && isset($_GET['proxy'])) {
    try {
        switch ($_GET['proxy']) {
            case 'years':
                $data = carapi_get('/api/years/v2');
                echo json_encode(['success' => true, 'data' => $data['data'] ?? $data]);
                break;

            case 'makes':
                $params = [];
                if (!empty($_GET['year'])) $params['year'] = (int) $_GET['year'];
                $data = carapi_get('/api/makes/v2', $params);
                echo json_encode(['success' => true, 'data' => $data['data'] ?? $data]);
                break;

            case 'models':
                $params = [];
                if (!empty($_GET['year']))    $params['year']    = (int) $_GET['year'];
                if (!empty($_GET['make_id'])) $params['make_id'] = (int) $_GET['make_id'];
                $data = carapi_get('/api/models/v2', $params);
                echo json_encode(['success' => true, 'data' => $data['data'] ?? $data]);
                break;

            case 'trims':
                $params = [];
                if (!empty($_GET['model_id'])) $params['model_id'] = (int) $_GET['model_id'];
                if (!empty($_GET['year']))      $params['year']     = (int) $_GET['year'];
                $data = carapi_get('/api/trims/v2', $params);
                echo json_encode(['success' => true, 'data' => $data['data'] ?? $data]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Unknown proxy target.']);
        }
    } catch (Throwable $e) {
        error_log('CarAPI proxy error: ' . $e->getMessage());
        http_response_code(502);
        echo json_encode(['error' => 'CarAPI unavailable: ' . $e->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// Standard fleet CRUD (our database)
// ─────────────────────────────────────────────────────────────────────────────
try {
    if ($method === 'GET') {
        // 1. Fetch our fleet from DB
        $fleet = $pdo->query("
            SELECT id, car_api_trim_id, car_api_year, car_label, plate, rent_cost_per_day, status, created_at
            FROM fleet
            ORDER BY id DESC
        ")->fetchAll();

        // 2. For each car that has a trim_id, enrich with CarAPI specs (make, model, engine, etc.)
        //    We batch-fetch unique trim IDs to avoid hitting CarAPI once per row.
        $trimIds = array_filter(array_unique(array_column($fleet, 'car_api_trim_id')));
        $trimCache = [];

        if (!empty($trimIds)) {
            try {
                // CarAPI supports filtering by IDs via the 'ids' parameter (comma-separated)
                $data = carapi_get('/api/trims/v2', ['verbose' => 'yes', 'ids' => implode(',', $trimIds), 'limit' => 50]);
                $trims = $data['data'] ?? [];
                foreach ($trims as $trim) {
                    $trimCache[$trim['id']] = [
                        'make'        => $trim['make_model']['make']['name']  ?? null,
                        'model'       => $trim['make_model']['name']          ?? null,
                        'year'        => $trim['year']                        ?? null,
                        'trim_name'   => $trim['name']                        ?? null,
                        'engine'      => $trim['description']                 ?? null,
                        'body_style'  => $trim['bodies'][0]['style']          ?? null,
                        'doors'       => $trim['bodies'][0]['doors']          ?? null,
                        'image_url'   => null, // CarAPI does not provide images
                    ];
                }
            } catch (Throwable $e) {
                error_log('CarAPI trim fetch error: ' . $e->getMessage());
                // Non-fatal — return fleet without extra specs
            }
        }

        // 3. Merge DB row + CarAPI data
        $result = array_map(function($car) use ($trimCache) {
            $specs = $car['car_api_trim_id'] ? ($trimCache[$car['car_api_trim_id']] ?? null) : null;
            return [
                // Our DB fields
                'id'               => $car['id'],
                'car_label'        => $car['car_label'],
                'plate'            => $car['plate'],
                'rent_cost_per_day'=> (float) $car['rent_cost_per_day'],
                'status'           => $car['status'],
                'created_at'       => $car['created_at'],
                'car_api_trim_id'  => $car['car_api_trim_id'],
                'car_api_year'     => $car['car_api_year'],
                // Enriched from CarAPI (may be null if no trim linked or API unavailable)
                'make'             => $specs['make']        ?? null,
                'model'            => $specs['model']       ?? null,
                'trim_name'        => $specs['trim_name']   ?? null,
                'engine'           => $specs['engine']      ?? null,
                'body_style'       => $specs['body_style']  ?? null,
                'doors'            => $specs['doors']       ?? null,
            ];
        }, $fleet);

        echo json_encode(['success' => true, 'data' => $result]);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        if (empty($data['car_label'])) {
            http_response_code(400);
            echo json_encode(['error' => 'car_label is required.']);
            exit;
        }

        // Auto-calculate a rent cost based on the year (e.g., $40 base + $3 for every year after 2000)
        $year = isset($data['car_api_year']) ? (int)$data['car_api_year'] : 2020;
        $calculated_cost = 40 + max(0, ($year - 2000) * 3);

        // Auto-generate a random plate if not provided
        $plate = !empty($data['plate']) ? trim($data['plate']) : 'DRIVE-' . strtoupper(substr(uniqid(), -5));

        $pdo->prepare("
            INSERT INTO fleet (car_api_trim_id, car_api_year, car_label, plate, rent_cost_per_day, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['car_api_trim_id']   ?? null,
            $data['car_api_year']       ?? null,
            trim($data['car_label']),
            $plate,
            $calculated_cost,
            $data['status'] ?? 'available',
        ]);

        echo json_encode(['success' => true, 'message' => 'Vehicle added to fleet.', 'id' => $pdo->lastInsertId()]);

    } elseif ($method === 'PATCH') {
        // Update availability status only
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($data['id']) || empty($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id and status are required.']);
            exit;
        }
        $allowed = ['available', 'booked', 'maintenance'];
        if (!in_array($data['status'], $allowed, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'status must be one of: ' . implode(', ', $allowed)]);
            exit;
        }
        $pdo->prepare("UPDATE fleet SET status = ? WHERE id = ?")->execute([$data['status'], $data['id']]);
        echo json_encode(['success' => true, 'message' => 'Status updated.']);

    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($data['id'])) { http_response_code(400); echo json_encode(['error' => 'Missing fleet vehicle ID.']); exit; }
        $pdo->prepare("DELETE FROM fleet WHERE id = ?")->execute([$data['id']]);
        echo json_encode(['success' => true, 'message' => 'Vehicle removed from fleet.']);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed.']);
    }

} catch (Throwable $e) {
    error_log('Fleet API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}