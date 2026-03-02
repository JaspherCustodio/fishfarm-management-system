<?php

function get_all_net_repairing($conn) {
        // Safety: session must exist
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isAdmin = ($_SESSION['role'] === 'admin');
    $userId  = intval($_SESSION['id']);

    $whereAssigned = "";
    if (!$isAdmin) {
        $whereAssigned = "AND nr.assigned_to = $userId";
    }
    $sql = "
        SELECT 
            nr.id,
            nr.start_date,
            nr.start_time,
            nr.end_date,
            nr.end_time,
            nr.status,
            nr.assigned_to,
            u.name AS assigned_name,
            s.schedule_datetime,
            fc.cage_name
        FROM net_repairing nr
        JOIN schedules s ON s.id = nr.schedule_id
        JOIN fish_cages fc ON fc.id = s.fish_cage
        LEFT JOIN users u ON u.id = nr.assigned_to
        WHERE s.task = 'Net Repairing'
        $whereAssigned
        ORDER BY s.schedule_datetime DESC
    ";

    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
