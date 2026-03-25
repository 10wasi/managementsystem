<?php
declare(strict_types=1);

function getSetting(string $key): ?string
{
    $row = fetchOne('SELECT value FROM settings WHERE key = :key LIMIT 1', ['key' => $key]);
    if ($row === null || !isset($row['value'])) {
        return null;
    }
    return (string) $row['value'];
}

function setSetting(string $key, string $value): bool
{
    $db = getDB();
    $stmt = $db->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)');
    return $stmt->execute([$key, $value]);
}