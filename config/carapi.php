<?php
/**
 * CarAPI Proxy Helper
 * 
 * Authenticates with carapi.app using token + secret,
 * caches the JWT for 6 days, and exposes a single helper
 * function to make authenticated GET requests.
 *
 * Usage:
 *   $data = carapi_get('/api/makes/v2');
 *   $data = carapi_get('/api/models/v2', ['year' => 2022, 'make_id' => 57]);
 */

define('CARAPI_TOKEN',  '481e0e1f-51d9-46c9-9cd8-2dad6faf5f96');
define('CARAPI_SECRET', 'c5248be0408633b5c3e676e2d496bc21');
define('CARAPI_BASE',   'https://carapi.app');
define('CARAPI_JWT_CACHE', __DIR__ . '/../.carapi_jwt.cache');

/**
 * Returns a valid JWT. Authenticates only when the cached one has expired.
 */
function carapi_jwt(): string {
    // Use cached JWT if still fresh (< 6 days old)
    if (file_exists(CARAPI_JWT_CACHE)) {
        $cached = json_decode(file_get_contents(CARAPI_JWT_CACHE), true);
        if ($cached && isset($cached['jwt'], $cached['expires_at']) && time() < $cached['expires_at']) {
            return $cached['jwt'];
        }
    }

    // Authenticate to get a fresh JWT
    $ch = curl_init(CARAPI_BASE . '/api/auth/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'api_token'  => CARAPI_TOKEN,
            'api_secret' => CARAPI_SECRET,
        ]),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        throw new RuntimeException('CarAPI auth failed. HTTP ' . $httpCode . ': ' . $response);
    }

    $jwt = trim($response, '"'); // CarAPI returns the JWT as a plain JSON string
    if (empty($jwt)) {
        throw new RuntimeException('CarAPI returned an empty JWT.');
    }

    // Cache for 6 days (JWT is valid for 7 days)
    file_put_contents(CARAPI_JWT_CACHE, json_encode([
        'jwt'        => $jwt,
        'expires_at' => time() + (6 * 24 * 3600),
    ]));

    return $jwt;
}

/**
 * Makes an authenticated GET request to the CarAPI.
 *
 * @param  string $path    e.g. '/api/makes/v2'
 * @param  array  $params  Optional query string parameters
 * @return array  Decoded JSON response
 */
function carapi_get(string $path, array $params = []): array {
    $url = CARAPI_BASE . $path;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . carapi_jwt(),
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new RuntimeException('CarAPI request failed. HTTP ' . $httpCode . ': ' . $path);
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('CarAPI returned invalid JSON for: ' . $path);
    }

    return $decoded;
}
