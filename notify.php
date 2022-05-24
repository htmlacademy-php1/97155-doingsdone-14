<?php
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

require_once 'vendor/autoload.php';
require_once 'init.php';

$dsn = 'smtp://redvoronik@gmail.com:z%+wM$"7.>BHbk@smtp.gmail.com:465?encryption=ssl&auth_mode=login';
$transport = Transport::fromDsn($dsn);

$mailer = new Mailer($transport);

$sql = "SELECT * FROM doingsdone.tasks WHERE done = 0 AND date_done = CURRENT_DATE()";

$result = mysqli_query($connection, $sql);
if ($result && mysqli_num_rows($result)) {
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$user_ids = array_column($tasks, 'user_id');
$user_ids = array_unique($user_ids);
$user_ids = array_values($user_ids);
var_dump($user_ids);

