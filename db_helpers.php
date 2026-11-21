<?php
/**
 * db_helpers.php — Safe query execution using prepared statements.
 * 
 * Usage examples:
 *   safe_query($conn, "INSERT INTO user (name, phone) VALUES (?, ?)", "ss", [$name, $phone]);
 *   $result = safe_select($conn, "SELECT * FROM user WHERE phone=?", "s", [$phone]);
 *   while ($row = $result->fetch_assoc()) { ... }
 *
 * Type string characters for bind_param:
 *   s = string, i = integer, d = double, b = blob
 */

function safe_query($conn, $sql, $types = '', $params = []) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

function safe_select($conn, $sql, $types = '', $params = []) {
    $stmt = safe_query($conn, $sql, $types, $params);
    return $stmt->get_result();
}
?>
