<?php
    header('Content-type: json/application; charset=utf-8');
    $host = "localhost";
    $db_name = "l90858az_queue";
    $username = "l90858az_queue";
    $password = "Slava2012";
    $connect = mysqli_connect($host, $username, $password, $db_name);
    $connect->set_charset("utf8");

    $sql = mysqli_query($connect, "SELECT * FROM `disciplines`");
    $res = array();
    while ($result = mysqli_fetch_array($sql)){
        $discipline = [
            "discipline_id" => $result['discipline_id'],
            "discipline_name" => $result['discipline_name']
        ];
        $res[] = $discipline;
    }
    $array = array(
        'disciplines' => $res,
    );
    echo json_encode($array);
    exit();
?>