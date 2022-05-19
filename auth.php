<?php
require_once 'init.php';

if (isset($_SESSION['id'])) {
    header("Location: /");
    exit();
} else {
    // проверяем была ли отправка формы авторизации
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // валидируем поля формы
        $errors = validate_authorization_form($connection, $_POST);

        // проверяем если массив с ошибками не пустой, то передаем в шаблон ошибки
        if (count($errors)) {
            $page_content = include_template('auth.php', ['errors' => $errors, 'connection' => $connection]);
        } else {
            $user_data = $_POST;

            if (isset($user_data['email'])) {
                // ищем пользователя в базе
                $user = find_user($connection, $user_data['email']);
            } else {
                $errors['email'] = 'Email пользователя не был передан';
            }
            // если пользователя не нашли, выдаем ошибку, если нашли сравниваем пароль
            if ($user === null) {
                $errors['email'] = 'Такой пользователь не найден';
            } elseif (password_verify($user_data['password'], $user['password'])) {
                $_SESSION = $user;

            } else {
                $errors['password'] = 'Неверный пароль';
            }

            //если появились ошибки, выводим их на странице формы, если ошибок нет, редирект на главную
            if (count($errors)) {
                $page_content = include_template('auth.php', ['errors' => $errors, 'connection' => $connection]);
            } else {
                header("Location: /");
                exit();
            }
        }
    } else {
        $page_content = include_template('auth.php', ['connection' => $connection]);
    }

    $layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
    print($layout_content);
}
