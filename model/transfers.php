<?php

function get_all_transfers($conn) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isAdmin = ($_SESSION['role'] === 'admin');
    $userId  = intval($_SESSION['id']);

    $whereAssigned = "";
    if (!$isAdmin) {
        $whereAssigned = "AND t.assigned_to = $userId";
    }

    $sql = "
        SELECT
            t.id,
            s.id AS stocking_ref,
            s.schedule_id AS stocking_schedule_id,  
            s.fish_type,
            t.from_cage AS from_cage_id,
            t.to_cage AS to_cage_id,
            fc_from.cage_name AS from_cage,
            fc_to.cage_name AS to_cage,
            sc.schedule_datetime,
            t.date_transferred,
            t.quantity_before,
            t.quantity_after,
            (t.quantity_before - t.quantity_after) AS mortality,
            t.remarks,
            t.status,
            t.assigned_to,
            u.name AS assigned_name
        FROM transfers t
        JOIN stocking s ON s.id = t.stocking_id
        JOIN schedules sc ON sc.id = s.schedule_id
        JOIN fish_cages fc_from ON fc_from.id = t.from_cage
        JOIN fish_cages fc_to ON fc_to.id = t.to_cage
        LEFT JOIN users u ON u.id = t.assigned_to
        WHERE 1=1
        $whereAssigned
        ORDER BY s.schedule_id DESC
    ";

    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}