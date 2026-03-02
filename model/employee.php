<?php
function get_all_employees($conn) {
    $sql = "SELECT id, name FROM users WHERE role = 'user'";
    $result = $conn->query($sql);

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
