<?php
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

require_once 'vendor/autoload.php';
require_once 'init.php';

$dsn = 'smtp://c3df02ef16878f:c6b0bceb69364f@smtp.mailtrap.io:2525?encryption=tls&auth_mode=login';
$transport = Transport::fromDsn($dsn);

$mailer = new Mailer($transport);

// получаем задачи для уведомлений
$tasks = get_tasks_for_notify($connection);

// подготавливаем массив получателей уведомлений
foreach ($tasks as $task) {
    $recipients[$task['user_id']]['email'] = $task['email'];
    $recipients[$task['user_id']]['user_name'] = $task['user_name'];
    $recipients[$task['user_id']]['tasks'][] = ['name' => $task['name'], 'date' => $task['date_done']];
}

// Формируем сообщение
$message = new Email();
foreach ($recipients as $recipient) {
    $message->to($recipient['email']);
    $message->from("keks@phpdemo.ru");
    $message->subject("Уведомление от сервиса «Дела в порядке»");
    if (count($recipient['tasks']) === 1) {
        $recipient['tasks'][0]['date'] = date("d.m.Y");
        $message_text = "Уважаемый, " . $recipient['user_name'] . "\n" . "У вас запланирована задача: \n";
        $message_text = $message_text . "- " . $recipient['tasks'][0]['name'] . " на " . $recipient['tasks'][0]['date'];

    } else {
        $message_text = "Уважаемый, " . $recipient['user_name'] . "\n" . "У вас запланированы задачи: \n";
        foreach ($recipient['tasks'] as $task) {
            $task['date'] = date("d.m.Y");
            $message_text = $message_text . "- " . $task['name'] . " на " . $task['date'] . "\n";
        }
    }

$message->text($message_text);

    // Отправка сообщения
    $mailer = new Mailer($transport);
    $mailer->send($message);
}
