<?php
require_once 'functions.php';

// подключаемся к базе данных
$config = require_once 'config.php';
$connection = db_connection($config['db']);

// получаем список проектов для пользователя
$projects = get_projects($connection);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_task = $_POST;
    $sql = "INSERT INTO tasks (name, project_id, date_done, user_id) VALUES (?, ?, ?, 1)";

    $stmt = db_get_prepare_stmt($connection, $sql, $new_task);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        header("Location: /");
    }
}

$page_content = include_template('add.php', ['projects' => $projects, 'connection' => $connection]);

$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
print($layout_content);

?>
