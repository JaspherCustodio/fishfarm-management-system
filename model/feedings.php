<?php
function get_all_feedings($conn) {
    // Safety: session must exist
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isAdmin = ($_SESSION['role'] === 'admin');
    $userId  = intval($_SESSION['id']);

    $whereAssigned = "";
    if (!$isAdmin) {
        $whereAssigned = "AND s.assigned_to = $userId";
    }
    $sql = "
        SELECT
            f.*,
            s.schedule_datetime,
            u.name AS assigned_name,
            fc.cage_name
        FROM feedings f
        JOIN schedules s ON s.id = f.schedule_id
        JOIN fish_cages fc ON fc.id = f.cage_id
        LEFT JOIN users u ON u.id = f.assigned_to
        WHERE s.task = 'Feeding'
        $whereAssigned
        ORDER BY s.schedule_datetime DESC, f.fed_time ASC
    ";

    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

