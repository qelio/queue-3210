import telebot
import requests_api
from telebot import types

bot = telebot.TeleBot('6748711781:AAHFk242Va9LX-CjKMAZBaQnOi-BwbL0LAs')

print("Telegram Bot is started!")
dictionary_users = {}
dictionary_users_attempt = {}


# Setting the response to the message /start
@bot.message_handler(commands=['start'])
def start_message(message):
    global dictionary_users, dictionary_users_attempt
    if dictionary_users.get(message.chat.id, 0) == 0:
        dictionary_users[message.chat.id] = message.chat.username
    if dictionary_users_attempt.get(message.chat.id, 0) == 0:
        dictionary_users_attempt[message.chat.id] = 1
    # If this account is linked to an account on the site:
    if not (requests_api.check_student(dictionary_users[message.chat.id])):
        inline_mark_up = types.InlineKeyboardMarkup()
        inline_mark_up.add(
            types.InlineKeyboardButton("Перейти на сайт", url='https://testmatica.ru/queue-3210/profile.php'))
        inline_mark_up.add(
            types.InlineKeyboardButton("Проверить привязку к аккаунту", callback_data='update_account_status' + ''))
        bot.send_message(message.chat.id,
                         "<b>Привет! Это Telegram Bot автоматизированной очереди \"QUEUE-3210\".\n\n</b>Для того, "
                         "чтобы привязать ваш Telegram-аккаунт к учетной записи на сайте, необходимо перейти по "
                         "следующему адресу и нажать кнопку \"Добавить аккаунт\": "
                         "https://testmatica.ru/queue-3210/profile.php",
                         parse_mode='html', reply_markup=inline_mark_up)
    else:
        student = requests_api.get_student_info(dictionary_users[message.chat.id])
        disciplines = requests_api.get_discipline_list()
        inline_mark_up = types.InlineKeyboardMarkup()
        for discipline in disciplines:
            inline_mark_up.add(types.InlineKeyboardButton(discipline.discipline_name,
                                                          callback_data="select_discipline_" + str(
                                                              discipline.discipline_id)))
        bot.send_message(message.chat.id,
                         "<b>Привет, " + student.student_name + " " + student.student_surname + "!</b>\n\nДля просмотра текущего состояния очереди, записи в очередь или удаления из нее, выберите одну из предложенных ниже дисциплин.",
                         parse_mode='html', reply_markup=inline_mark_up)


@bot.callback_query_handler(func=lambda callback: True)
def check_status_account(callback):
    global dictionary_users, dictionary_users_attempt
    if callback.data == 'update_account_status':
        if not (requests_api.check_student(dictionary_users[callback.message.chat.id])):
            inline_mark_up = types.InlineKeyboardMarkup()
            inline_mark_up.add(
                types.InlineKeyboardButton("Проверить привязку к аккаунту", callback_data='update_account_status'))
            bot.edit_message_text(f"На данный момент ни одная учетная запись на сайте не привязана к данному "
                                  "Telegram-аккаунту (попытка №" + str(
                dictionary_users_attempt[callback.message.chat.id]) + ")",
                                  callback.message.chat.id,
                                  callback.message.message_id, parse_mode='html', reply_markup=inline_mark_up)
            dictionary_users_attempt[callback.message.chat.id] += 1
        else:
            dictionary_users[callback.message.chat.id] = callback.from_user.username
            bot.delete_message(callback.message.chat.id, callback.message.message_id)
            dictionary_users_attempt[callback.message.chat.id] = 1
            start_message(callback.message)
    elif callback.data[:17] == "select_discipline":
        if dictionary_users.get(callback.message.chat.id, 0) != 0:
            if requests_api.check_student(dictionary_users[callback.message.chat.id]):
                info_discipline(callback, callback.data[18:])
            else:
                start_message(callback.message)
        else:
            start_message(callback.message)
    elif callback.data == "comeback":
        bot.delete_message(callback.message.chat.id, callback.message.message_id)
        start_message(callback.message)
    elif callback.data[:12] == "update_queue":
        info_discipline(callback, callback.data[13:])
    elif callback.data[:8] == "queue_dl":
        if dictionary_users.get(callback.message.chat.id, 0) != 0:
            if requests_api.check_student(dictionary_users[callback.message.chat.id]):
                if requests_api.set_shared_queue(callback.data[9:], dictionary_users[callback.message.chat.id],
                                                 "delete"):
                    info_discipline(callback, callback.data[9:])
                else:
                    bot.delete_message(callback.message.chat.id, callback.message.message_id)
                    start_message(callback.message)
            else:
                start_message(callback.message)
        else:
            start_message(callback.message)
    elif callback.data[:8] == "queue_wr":
        if dictionary_users.get(callback.message.chat.id, 0) != 0:
            if requests_api.check_student(dictionary_users[callback.message.chat.id]):
                if requests_api.set_shared_queue(callback.data[9:], dictionary_users[callback.message.chat.id], "add"):
                    info_discipline(callback, callback.data[9:])
                else:
                    bot.delete_message(callback.message.chat.id, callback.message.message_id)
                    start_message(callback.message)
            else:
                start_message(callback.message)
        else:
            start_message(callback.message)


def info_discipline(callback, discipline_id):
    global dictionary_users, dictionary_users_attempt
    if dictionary_users.get(callback.message.chat.id, 0) != 0:
        if requests_api.check_student(dictionary_users[callback.message.chat.id]):
            bot.delete_message(callback.message.chat.id, callback.message.message_id)
            inline_mark_up = types.InlineKeyboardMarkup()
            disciplines = requests_api.get_discipline_list()
            shared_queue = requests_api.get_shared_queue(discipline_id)
            for discipline in disciplines:
                if discipline.discipline_id == discipline_id:
                    discipline_name = discipline.discipline_name
            title_message = "<b>ОБЩАЯ ОЧЕРЕДЬ ПО ДИСЦИПЛИНЕ \"" + str(discipline_name).upper() + "\":\n\n</b>";
            title_queue = ""
            count_pos = 1
            student_in_queue = False
            for queue_pos in shared_queue:
                if queue_pos.tg_login == dictionary_users[callback.message.chat.id]:
                    title_queue += "<b>" + str(count_pos) + ". " + str(queue_pos.students_student_surname) + " " + str(
                        queue_pos.students_student_name) + " (Вы)\n🕓 " + str(queue_pos.shared_queue_date) + "</b>\n\n"
                    student_in_queue = True
                else:
                    title_queue += str(count_pos) + ". " + str(queue_pos.students_student_surname) + " " + str(
                        queue_pos.students_student_name) + " \n🕓 " + str(queue_pos.shared_queue_date) + "\n\n"
                count_pos += 1
            if count_pos == 1:
                title_queue = "На данный момент очередь пуста!"
            if student_in_queue:
                inline_mark_up.add(
                    types.InlineKeyboardButton("Удалиться из очереди", callback_data='queue_dl_' + str(discipline_id)))
            else:
                inline_mark_up.row(
                    types.InlineKeyboardButton("Записаться в очередь", callback_data='queue_wr_' + str(discipline_id)))
            inline_mark_up.row(
                types.InlineKeyboardButton("Обновить", callback_data='update_queue_' + str(discipline_id)),
                types.InlineKeyboardButton("Назад", callback_data='comeback'))
            bot.send_message(callback.message.chat.id, title_message + title_queue, parse_mode='html',
                             reply_markup=inline_mark_up)
        else:
            start_message(callback.message)
    else:
        start_message(callback.message)


bot.polling(none_stop=True)
