<?php

function get_all_samplings($conn) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isAdmin = ($_SESSION['role'] === 'admin');
    $userId  = intval($_SESSION['id']);

    $whereAssigned = "";
    if (!$isAdmin) {
        $whereAssigned = "AND sp.assigned_to = $userId";
    }

    $sql = "
        SELECT 
            sp.*,
            sch.schedule_datetime,
            fc.cage_name,
            u.name AS assigned_name
        FROM samplings sp
        JOIN schedules sch ON sch.id = sp.schedule_id
        JOIN fish_cages fc ON fc.id = sch.fish_cage
        LEFT JOIN users u ON u.id = sp.assigned_to
        WHERE sch.task = 'Sampling'
        $whereAssigned
        ORDER BY sch.schedule_datetime DESC
    ";

    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}