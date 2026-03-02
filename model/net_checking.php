<?php
function get_all_net_checking($conn) {
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
            nc.id,
            nc.start_date,
            nc.start_time,
            nc.end_date,
            nc.end_time,
            nc.status,
            nc.assigned_to,
            u.name AS assigned_name,
            s.schedule_datetime,
            fc.cage_name
        FROM net_checking nc
        JOIN schedules s ON s.id = nc.schedule_id
        JOIN fish_cages fc ON fc.id = s.fish_cage
        LEFT JOIN users u ON u.id = nc.assigned_to
        WHERE s.task = 'Net Checking'
        $whereAssigned
        ORDER BY s.schedule_datetime DESC
    ";

    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
