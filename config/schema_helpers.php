<?php
/** Database compatibility helpers for both supported DriveEase schema versions. */
function tableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare('SHOW TABLES LIKE ?');
    $statement->execute([$table]);
    return (bool) $statement->fetchColumn();
}

function columnsFor(PDO $pdo, string $table): array
{
    $statement = $pdo->query("SHOW COLUMNS FROM `{$table}`");
    return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'Field');
}

function firstExistingTable(PDO $pdo, array $tables): ?string
{
    foreach ($tables as $table) {
        if (tableExists($pdo, $table)) {
            return $table;
        }
    }
    return null;
}

function requireTable(PDO $pdo, array $tables, string $label): string
{
    $table = firstExistingTable($pdo, $tables);
    if ($table === null) {
        throw new RuntimeException("The {$label} table has not been created yet.");
    }
    return $table;
}