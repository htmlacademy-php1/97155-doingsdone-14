<?php
require_once 'init.php';

// проверяем была ли отправка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // валидируем поля формы
    $errors = validate_registration_form($connection, $_POST);

    // проверяем если массив с ошибками не пустой, то передаем в шаблон ошибки
    if (count($errors)) {
        $page_content = include_template('register.php', ['errors' => $errors, 'connection' => $connection]);
    } else {
        $new_user = $_POST;

        // добавляем пользователя в базу
        $result = add_user($connection, $new_user);

        // если новый пользователь добавлен в базу успешно, переадрисоываем пользователя на главную
        if ($result) {
            header("Location: /");
        }
    }
} else {
    $page_content = include_template('register.php', ['connection' => $connection]);
}

$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
print($layout_content);

