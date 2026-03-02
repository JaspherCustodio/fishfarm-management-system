<?php

function get_all_fish_cages($conn) {
    $sql = "SELECT * FROM fish_cages ORDER BY date_added DESC";
    $result = $conn->query($sql);

    return $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
