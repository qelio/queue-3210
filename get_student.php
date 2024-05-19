<?php
    header('Content-type: json/application; charset=utf-8');
    $host = "localhost";
    $db_name = "l90858az_queue";
    $username = "l90858az_queue";
    $password = "Slava2012";
    $connect = mysqli_connect($host, $username, $password, $db_name);
    $connect->set_charset("utf8");
    $auth_flag = false;

    if (isset($_POST['tg_login']) && isset($_GET['auth_key'])) {
        $sql = mysqli_query($connect, "SELECT * FROM `auth_keys` WHERE `auth_key` = '{$_GET['auth_key']}'");
        if ($result = mysqli_fetch_array($sql)){
            $auth_flag = true;
        }
        if ($auth_flag == true) {
            $sql = mysqli_query($connect, "SELECT * FROM `students` WHERE `tg_login` = '{$_POST['tg_login']}'");
            if ($result = mysqli_fetch_array($sql)){
                $res = array();
                $student = [
                    "code" => "100",
                    "student_id" => $result['student_id'],
                    "student_login" => $result['student_login'],
                    "student_name" => $result['student_name'],
                    "student_surname" => $result['student_surname'],
                    "student_password" => $result['student_password'],
                    "student_date_of_registration" => $result['student_date_of_registration'],
                    "student_device" => $result['student_device'],
                    "student_privileges" => $result['student_privileges'],
                    "tg_login" => $result['tg_login']
                ];
                $res[] = $student;
                $array = array(
                    'get_student' => $res,
                );
                echo json_encode($array);
                exit();
            }
            else {
                $res = [
                    "code" => 101,
                    "message" => "The user with this TG ID was not found!"
                ];
                $res = array($res);
                $array = array(
                    'get_student' => $res,
                );
                echo json_encode($array);
                exit();
            }
        }
        else {
            $res = [
                "code" => 111,
                "message" => "Invalid authorization key!"
            ];
            $res = array($res);
            $array = array(
                'get_student' => $res,
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
            'get_student' => $res,
        );
        echo json_encode($array);
        exit();
    }
?>