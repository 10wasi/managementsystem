<?php
declare(strict_types=1);

/**
 * Execute a prepared statement.
 */
function runQuery(string $sql, array $params = []): PDOStatement
{
    $db = null;
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        $db = $GLOBALS['pdo'];
    } elseif (function_exists('getDB')) {
        $db = getDB();
    }

    if (!$db instanceof PDO) {
        throw new RuntimeException('Database connection is not initialized.');
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch a single row.
 */
function fetchOne(string $sql, array $params = []): ?array
{
    $row = runQuery($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/**
 * Fetch all rows.
 */
function fetchAll(string $sql, array $params = []): array
{
    return runQuery($sql, $params)->fetchAll();
}

/**
 * Insert a record and return new ID.
 */
function insert(string $table, array $data): int
{
    $columns = array_keys($data);
    $placeholders = array_map(static fn(string $col): string => ':' . $col, $columns);

    $sql = 'INSERT INTO ' . $table
        . ' (' . implode(', ', $columns) . ')'
        . ' VALUES (' . implode(', ', $placeholders) . ')';

    runQuery($sql, $data);
    $db = isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO ? $GLOBALS['pdo'] : getDB();
    return (int) $db->lastInsertId();
}

/**
 * Update matching rows and return affected count.
 */
function update(string $table, array $data, string $where, array $whereParams = []): int
{
    $setParts = [];
    $params = [];

    foreach ($data as $column => $value) {
        $key = 'set_' . $column;
        $setParts[] = $column . ' = :' . $key;
        $params[$key] = $value;
    }

    foreach ($whereParams as $key => $value) {
        $params[$key] = $value;
    }

    $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $setParts) . ' WHERE ' . $where;
    return runQuery($sql, $params)->rowCount();
}

/**
 * Delete matching rows and return affected count.
 */
function delete(string $table, string $where, array $whereParams = []): int
{
    $sql = 'DELETE FROM ' . $table . ' WHERE ' . $where;
    return runQuery($sql, $whereParams)->rowCount();
}
