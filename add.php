<?php
require_once 'functions.php';

// подключаемся к базе данных
$config = require_once 'config.php';
$connection = db_connection($config['db']);

$projects = get_projects($connection);

// проверяем была ли отправка формы добавления новой задачи
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = validate_task_form($connection, $_POST);

// проверяем если массив с ошибками не пустой, то передаем в шаблон ошибки
    if (count($errors)) {
        $page_content = include_template('add.php', ['projects' => $projects, 'errors' => $errors, 'connection' => $connection]);
    } else {
        $new_task = $_POST;

        // если при отправке формы добавили файл, переносим его в папку /uploads
        if ($_FILES['file']['size'] != 0) {
            $file_name = $_FILES['file']['name'];
            $file_path = __DIR__ . '/uploads/';
            $file_url = '/uploads/' . $file_name;
            $new_task['file'] = $file_url;
            move_uploaded_file($_FILES['file']['tmp_name'], $file_path . $file_name);
        } else {
            $new_task['file'] = null;
        }

        // подготовленное выражение для запроса на добавление новой задачи в базу
        $sql = "INSERT INTO tasks (name, project_id, date_done, user_id, file) VALUES (?, ?, ?, 1, ?)";

        $stmt = db_get_prepare_stmt($connection, $sql, $new_task);
        $result = mysqli_stmt_execute($stmt);

        // если новая задача добавлена в базу успешно, переадрисоываем пользователя на главную
        if ($result) {
            header("Location: /");
        }
    }
} else {
    $page_content = include_template('add.php', ['projects' => $projects, 'connection' => $connection]);
}

$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
print($layout_content);

?>
