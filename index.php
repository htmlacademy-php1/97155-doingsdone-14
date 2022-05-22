<?php
require_once 'init.php';

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

if (isset($_SESSION['name'])) {
    // получаем список проектов для пользователя
    $projects = get_projects ($connection, $_SESSION['id']);

    // получаем список задач для пользователя
    $project_id = (int)filter_input(INPUT_GET, 'project_id');
    $tasks = get_tasks($connection, $project_id, $_SESSION['id']);

    // переводим формат даты выполнения задачи к виду dd-mm-yyyy
    foreach ($tasks as &$task) {
        $date_done = date_convert($task['date_done']);
        $task['date_done'] = $date_done;
    }

    // если у задачи отмечается чекбокс, меняем статус задачи
    if (isset($_GET['task_id'])) {
        $status = change_task_status($connection, $_GET['task_id'], $_SESSION['id']);

        if ($status) {
            header("Location: /");
            exit();
        }
    }

    // если была отправлена форма поиска, делаем поиск по задачам пользователя
    if (isset($_GET['q'])) {
        $search = get_search($connection, $_GET['q'], $_SESSION['id']);
        $page_content = include_template('main.php', ['projects' => $projects, 'tasks' => $search, 'show_complete_tasks' => $show_complete_tasks, 'connection' => $connection]);
    } else {
        $page_content = include_template('main.php', ['projects' => $projects, 'tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks, 'connection' => $connection]);
    }
} else {
    $page_content = include_template('guest.php', []);
}
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
print($layout_content);
