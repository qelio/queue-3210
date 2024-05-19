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

    if (isset($_POST['discipline_id']) && isset($_GET['auth_key'])) {
        $sql = mysqli_query($connect, "SELECT * FROM `auth_keys` WHERE `auth_key` = '{$_GET['auth_key']}'");
        if ($result = mysqli_fetch_array($sql)){
            $auth_flag = true;
        }
        if ($auth_flag == true) {
            $res = array();
            $count = 0;
            $sql = mysqli_query($connect, "SELECT `shared_queue`.*, `students`.`tg_login` FROM `shared_queue` JOIN `students` ON `students`.`student_id` = `shared_queue`.`students_student_id`	WHERE `shared_queue`.`shared_queue_status` = '1' AND `shared_queue`.`disciplines_discipline_id` = '{$_POST['discipline_id']}'");
            while ($result = mysqli_fetch_array($sql)){
                $shared_queue = [
                    "shared_queue_id" => $result['shared_queue_id'],
                    "students_student_id" => $result['students_student_id'],
                    "disciplines_discipline_id" => $result['disciplines_discipline_id'],
                    "shared_queue_date" => $result['shared_queue_date'],
                    "shared_queue_status" => $result['shared_queue_status'],
                    "tg_login" => $result['tg_login']
                ];
                $res[] = $shared_queue;
                $count += 1;
            }
            if ($count > 0) {
                $array = array(
                    'get_shared_queue' => $res,
                );
                echo json_encode($array);
                exit();
            }
            $res = [
                "code" => 101,
                "message" => "No relevant records were found!"
            ];
            $res = array($res);
            $array = array(
                'get_shared_queue' => $res,
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
                'get_shared_queue' => $res,
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
            'get_shared_queue' => $res,
        );
        echo json_encode($array);
        exit();
    }

?>