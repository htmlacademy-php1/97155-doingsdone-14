<?php
require_once 'functions.php';

// подключаемся к базе данных
$config = require_once 'config.php';
$connection = db_connection($config['db']);

// получаем список проектов для пользователя
$projects = get_projects($connection);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_task = $_POST;

    if (isset($_FILES['file']) && $_FILES['file']['size'] != 0) {
    $file_name = $_FILES['file']['name'];
    $file_path = __DIR__ . '/uploads/';
    $file_url = '/uploads/' . $file_name;
    $new_task['file'] = $file_url;

    move_uploaded_file($_FILES['file']['tmp_name'], $file_path . $file_name);

    // print("<a href="/php/14/book/06-forms/04-upload/$file_url">$file_name</a>");
}

    var_dump($new_task);
    print_r($_FILES);
    $sql = "INSERT INTO tasks (name, project_id, date_done, user_id, file) VALUES (?, ?, ?, 1, ?)";

    $stmt = db_get_prepare_stmt($connection, $sql, $new_task);
    $result = mysqli_stmt_execute($stmt);

    // if ($result) {
    //     header("Location: /");
    // }
}

$page_content = include_template('add.php', ['projects' => $projects, 'connection' => $connection]);

$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
print($layout_content);

?>
