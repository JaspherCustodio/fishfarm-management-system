<?php
function count_expenses($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM expenses");
    $row = $result->fetch_assoc();
    return $row['total'];
}

function get_expenses($conn) {
    // Just run a normal query
    $result = $conn->query("SELECT * FROM expenses ORDER BY expense_date DESC");
    if (!$result) {
        // Optional: for debugging
        die("Query failed: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}