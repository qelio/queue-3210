import requests
import Student
import Discipline
import SharedQueue


def check_student(tg_login):
    data = {'auth_key': 'KhyBVLzEPlamQvQqObBX', 'tg_login': tg_login}
    response = requests.post('https://testmatica.ru/queue-3210/api/get_student.php', data=data)
    response_json = response.json()
    if response_json["get_student"][0]["code"] == '100':
        return True
    else:
        return False


def get_student_info(tg_login):
    data = {'auth_key': 'KhyBVLzEPlamQvQqObBX', 'tg_login': tg_login}
    response = requests.post('https://testmatica.ru/queue-3210/api/get_student.php', data=data)
    response_json = response.json()
    response_json = response_json["get_student"][0]
    student = Student.Student(response_json["student_id"], response_json["student_login"],
                              response_json["student_name"], response_json["student_surname"],
                              response_json["student_password"], response_json["student_date_of_registration"],
                              response_json["student_device"], response_json["student_privileges"],
                              response_json["tg_login"])
    return student


def get_discipline_list():
    data = {'auth_key': 'KhyBVLzEPlamQvQqObBX'}
    response = requests.post('https://testmatica.ru/queue-3210/api/get_disciplines.php', data=data)
    response_json = response.json()
    disciplines = list()
    for discipline in response_json["get_disciplines"]:
        disciplines.append(Discipline.Discipline(discipline["discipline_id"], discipline["discipline_name"]))
    return disciplines


def get_shared_queue(discipline_id):
    data = {'auth_key': 'KhyBVLzEPlamQvQqObBX', 'discipline_id': discipline_id}
    response = requests.post('https://testmatica.ru/queue-3210/api/get_shared_queue.php', data=data)
    response_json = response.json()
    shared_queue = list()
    if response_json["get_shared_queue"][0]["code"] == 100:
        count_queue = 0
        for queue_pos in response_json["get_shared_queue"]:
            if count_queue != 0:
                shared_queue.append(
                    SharedQueue.SharedQueue(queue_pos["shared_queue_id"], queue_pos["students_student_id"],
                                            queue_pos["students_student_name"], queue_pos["students_student_surname"],
                                            queue_pos["disciplines_discipline_id"],
                                            queue_pos["shared_queue_date"], queue_pos["shared_queue_status"],
                                            queue_pos["tg_login"]))
            count_queue += 1
    return shared_queue


def set_shared_queue(discipline_id, tg_login, action):
    data = {'auth_key': 'KhyBVLzEPlamQvQqObBX', 'discipline_id': discipline_id, 'tg_login': tg_login, 'action': action}
    response = requests.post('https://testmatica.ru/queue-3210/api/action_shared_queue.php', data=data)
    response_json = response.json()
    if response_json["action_shared_queue"][0]["code"] == 100:
        return True
    else:
        return False
