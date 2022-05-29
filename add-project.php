<?php
require_once 'init.php';

if (isset($_SESSION['id'])) {
    $projects = get_projects($connection, $_SESSION['id']);

    // проверяем была ли отправка формы добавления проекта
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // валидируем поля формы
        $errors = validate_project_form($connection, $_POST, $_SESSION['id']);

        // проверяем если массив с ошибками не пустой, то передаем в шаблон ошибки
        if (count($errors)) {
            $page_content = include_template('add-project.php', ['projects' => $projects, 'errors' => $errors, 'connection' => $connection]);
        } else {
            $new_project = $_POST;
            // добавляем проект в базу
            $result = add_project($connection, $new_project, $_SESSION['id']);
            header("Location: /add-project.php");
            exit();
        }
    } else {
        $page_content = include_template('add-project.php', ['projects' => $projects, 'connection' => $connection]);
    }

    $layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
    print($layout_content);
} else {
    header("HTTP/1.1 403 Forbidden");
    exit();
}
