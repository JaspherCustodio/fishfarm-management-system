<?php 

function get_all_users($conn) {
    $sql = "SELECT * FROM users WHERE status = 'approved'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        return 0;
    }
}


function get_pending_users($conn) {
    $sql = "SELECT * FROM users WHERE status = 'pending'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        return 0;
    }
}



function get_approved_users($conn) {
    $sql = "SELECT * FROM users WHERE status = 'approved' ORDER BY id DESC";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : 0;
}
