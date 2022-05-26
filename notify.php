<?php
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

require_once 'vendor/autoload.php';
require_once 'init.php';

$dsn = 'smtp://c3df02ef16878f:c6b0bceb69364f@smtp.mailtrap.io:2525?encryption=tls&auth_mode=login';
$transport = Transport::fromDsn($dsn);

$mailer = new Mailer($transport);

$sql = "SELECT t.name, t.date_done, t.user_id, u.name AS user_name, u.email FROM tasks t LEFT JOIN users u ON t.user_id = u.id WHERE done = 0 AND date_done = CURRENT_DATE()";

$result = mysqli_query($connection, $sql);
if ($result && mysqli_num_rows($result)) {
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
}


$users_ids = array_column($tasks, 'user_id');
$users_ids = array_unique($users_ids);
$users_ids = array_values($users_ids);

$recipients = [];

foreach ($users_ids as $users_id) {
    foreach ($tasks as $key => $value) {
        if ($value['user_id'] === $users_id) {
            $sql_rec = "SELECT t.name, t.date_done, t.user_id, u.name AS user_name, u.email FROM tasks t LEFT JOIN users u ON t.user_id = u.id WHERE done = 0 AND date_done = CURRENT_DATE() AND user_id = $users_id";
        $result_rec = mysqli_query($connection, $sql_rec);
        $recipients = mysqli_fetch_all($result_rec, MYSQLI_ASSOC);
        }
    }
}


// Формирование сообщения
$message = new Email();
$message->to("keks@htmlacademy.ru");
$message->from("mail@giftube.academy");
$message->subject("Просмотры вашей гифки");
$message->text("Вашу гифку «Кот и пылесос» посмотрело больше 1 млн!");

// Отправка сообщения
$mailer = new Mailer($transport);
$mailer->send($message);
