<?php
/* fetch all records with assigned employee name */
function get_fish_cage_management($conn) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isAdmin = ($_SESSION['role'] === 'admin');
    $userId  = intval($_SESSION['id']);

    $whereAssigned = "";
    if (!$isAdmin) {
        $whereAssigned = "AND fcm.assigned_to = $userId"; // <-- use fcm.assigned_to
    }

    $sql = "
        SELECT 
            fcm.*,
            s.schedule_datetime,
            u.name AS assigned_name,
            fc.cage_name
        FROM fish_cage_management fcm
        JOIN schedules s ON s.id = fcm.schedule_id
        JOIN fish_cages fc ON fc.id = s.fish_cage
        LEFT JOIN users u ON u.id = fcm.assigned_to   -- <-- change this
        WHERE s.task = 'Fish Cage Management'
        $whereAssigned
        ORDER BY s.schedule_datetime DESC
    ";

    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}