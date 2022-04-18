<?php
require_once 'functions.php';

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// подключаемся к базе данных
$db = require_once 'config.php';
$connection = db_connection($db);

// получаем список проектов для пользователя
$projects = get_projects ($connection);

// получаем список задач для пользователя
$tasks = get_tasks ($connection);

// переводим формат даты выполнения задачи к виду dd-mm-yyyy
foreach ($tasks as &$task) {
    $date_done = date_convert($task['date_done']);
    $task['date_done'] = $date_done;
}

$page_content = include_template('main.php', ['projects' => $projects, 'tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks]);
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
print($layout_content);

?>
