<?php 
// SELECT `shared_queue`.*, `students`.`tg_login` FROM `shared_queue`
// JOIN `students` ON `students`.`student_id` = `shared_queue`.`students_student_id`	
// WHERE `students`.`tg_login` = 'qelio'

header('Content-type: json/application; charset=utf-8');
    $host = "localhost";
    $db_name = "l90858az_queue";
    $username = "l90858az_queue";
    $password = "Slava2012";
    $connect = mysqli_connect($host, $username, $password, $db_name);
    $connect->set_charset("utf8");
    $auth_flag = false;
    if (isset($_POST['discipline_id']) && isset($_POST['tg_login']) && isset($_POST['action']) && isset($_GET['auth_key'])) {
        $sql = mysqli_query($connect, "SELECT * FROM `auth_keys` WHERE `auth_key` = '{$_GET['auth_key']}'");
        if ($result = mysqli_fetch_array($sql)){
            $auth_flag = true;
        }
        if ($auth_flag == true) {
            $student_id = 0;
            $count_disciplines = 0;
            $current_date = date ("d.m.Y H:i");
            $sql = mysqli_query($connect, "SELECT * FROM `students` WHERE `tg_login` = '{$_POST['tg_login']}'");
            if ($result = mysqli_fetch_array($sql)){
                $student_id = $result['student_id'];
            }
            $sql = mysqli_query($connect, "SELECT * FROM `disciplines` WHERE `discipline_id` = '{$_POST['discipline_id']}'");
            if ($result = mysqli_fetch_array($sql)){
                $count_disciplines += 1;
            }
            if ($student_id != 0 && $count_disciplines == 1) {
                if ($_POST['action'] == "add") {
                    $sql = mysqli_query($connect, "REPLACE INTO `shared_queue` (`students_student_id`, `disciplines_discipline_id`, `shared_queue_date`, `shared_queue_status`) VALUES ('{$student_id}', '{$_POST['discipline_id']}', '{$current_date}', '1')");
                    if ($sql) {
                        $res = [
                            "code" => 100,
                            "message" => "The user was added successfully!"
                        ];
                        $res = array($res);
                        $array = array(
                            'action_shared_queue' => $res,
                        );
                        echo json_encode($array);
                        exit();
                    }
                }
                else if ($_POST['action'] == "delete") {
                    $sql = mysqli_query($connect, "UPDATE `shared_queue` SET `shared_queue_status` = '0' WHERE `students_student_id` = '{$student_id}'");
                    if ($sql) {
                        $res = [
                            "code" => 100,
                            "message" => "The user was deleted successfully"
                        ];
                        $res = array($res);
                        $array = array(
                            'action_shared_queue' => $res,
                        );
                        echo json_encode($array);
                        exit();
                    }
                }
                else {
                    $res = [
                        "code" => 103,
                        "message" => "The action is not defined!"
                    ];
                    $res = array($res);
                    $array = array(
                        'action_shared_queue' => $res,
                    );
                    echo json_encode($array);
                    exit();
                }
            }
            else {
                $res = [
                    "code" => 101,
                    "message" => "The user with this TG ID was not found or this discipline has not been found!"
                ];
                $res = array($res);
                $array = array(
                    'action_shared_queue' => $res,
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
                'action_shared_queue' => $res,
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
            'action_shared_queue' => $res,
        );
        echo json_encode($array);
        exit();
    }

?>