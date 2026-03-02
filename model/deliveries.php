<?php
function get_all_deliveries($conn) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isAdmin = ($_SESSION['role'] === 'admin');
    $userId  = intval($_SESSION['id']);

    $whereAssigned = "";
    if (!$isAdmin) {
        $whereAssigned = "WHERE d.assigned_to = $userId";
    }

    $sql = "
    SELECT
        d.id,
        d.stocking_id,
        d.assigned_to,
        d.delivery_date,
        d.quantity_delivered,
        d.buyer_name,
        d.sale_amount,
        d.remarks,
        d.status,

        s.fish_type,
        s.schedule_id,
        sc.schedule_datetime,
        fc.id AS cage_id,
        fc.cage_name,
        u.name AS assigned_name       -- <<< add this line

    FROM deliveries d
    JOIN stocking s ON s.id = d.stocking_id
    JOIN schedules sc ON sc.id = s.schedule_id
    JOIN fish_cages fc ON fc.id = d.cage_id
    LEFT JOIN users u ON u.id = d.assigned_to   -- <<< join to get the name
    $whereAssigned
    ORDER BY s.schedule_id DESC
";

    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}