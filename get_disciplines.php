<?php
    header('Content-type: json/application; charset=utf-8');
    $host = "localhost";
    $db_name = "l90858az_queue";
    $username = "l90858az_queue";
    $password = "Slava2012";
    $connect = mysqli_connect($host, $username, $password, $db_name);
    $connect->set_charset("utf8");
    $auth_flag = false;

    if (isset($_GET['auth_key'])) {
        $sql = mysqli_query($connect, "SELECT * FROM `auth_keys` WHERE `auth_key` = '{$_GET['auth_key']}'");
        if ($result = mysqli_fetch_array($sql)){
            $auth_flag = true;
        }
        if ($auth_flag == true) {
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
                'get_disciplines' => $res,
            );
            echo json_encode($array);
            exit();
        }
        else {
            $res = [
                "code" => 111,
                "message" => "Invalid authorization key!"
            ];
            $res = array($res);
            $array = array(
                'get_disciplines' => $res,
            );
            echo json_encode($array);
            exit();
        }
    }
    else {
        $res = [
            "code" => 102,
            "message" => "One of the required fields was not passed"
        ];
        $res = array($res);
        $array = array(
            'get_disciplines' => $res,
        );
        echo json_encode($array);
        exit();
    }
?>