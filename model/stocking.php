<?php

function get_all_stocking($conn) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isAdmin = ($_SESSION['role'] === 'admin');
    $userId  = intval($_SESSION['id']);

    $whereAssigned = "";
    if (!$isAdmin) {
        // We check the assignment on the schedule linked to the stocking
        $whereAssigned = "AND st.assigned_to = $userId";
    }

    $sql = "
        SELECT 
            st.*,
            s.schedule_datetime,
            fc.cage_name,
            u.name AS assigned_name
        FROM stocking st
        JOIN schedules s ON s.id = st.schedule_id
        JOIN fish_cages fc ON fc.id = s.fish_cage
        LEFT JOIN users u ON u.id = st.assigned_to
        WHERE (s.task = 'Stocking' OR st.source_of_fingerlings = 'Transfer')
        $whereAssigned
        ORDER BY st.created_at DESC
    ";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
