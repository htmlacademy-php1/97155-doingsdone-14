<?php


/**
 * Проверяет существование указанного проекта
 * @param $id int ID проекта
 * @param $allowed_list array Массив существующих проектов
 * @return string | null Если не находит проект, возвращает текст ошибки. Если находит, возвращает null
 */
function validate_project(int $id, array $allowed_list) : string | null {
    if (!in_array($id, $allowed_list)) {
        return "Указан несуществующий проект";
    }

    return null;
}

/**
 * Проверяет заполненность поля
 * @param $value string Содержимое поля
 * @return string Если поле пустое, возвращает ошибку, иначе null
 */
function validate_availability(string $value) : string | null {
    if ($value === "") {
        return "Поле должно быть заполнено";
    }

    return null;
}

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'. Проверяет, что дата больше или равна текущей.
 * @param string $date Дата в виде строки
 * @return null если совпадает форматом и дата больше или равна текущей, иначе текст ошибки
 */
function is_date_valid(string $date) : null | string {
    $format_to_check = 'Y-m-d';
    $current_date = date('Y-m-d');
    $current_date_time_obj = date_create_from_format($format_to_check, $current_date);
    $date_time_obj = date_create_from_format($format_to_check, $date);

    if ($date_time_obj < $current_date_time_obj) {
        return "Дата должна быть больше или равна текущей";
    }

    if ($date_time_obj !== false && array_sum(date_get_last_errors()) === 0) {
        return null;
    }

    return "Укажите дату в формате ГГГГ-ММ-ДД";
}

/**
 * Валидирует поля формы добавления задачи
 * @param mysqli $connection Объект с данными для подключения к базе
 * @param array $post Массив содержащий данные из полей формы
 * @param int $user_id ID пользователя
 * @return array массив с ошибками
 */
function validate_task_form(mysqli $connection, array $post, int $user_id) : array {
    // получаем список проектов для пользователя
    $projects = get_projects($connection, $user_id);
    $projects_ids = array_column($projects, 'id');

    // определяем массив правил для проверки полей формы
    $rules = [
        'name' => function($value) {
            return validate_availability($value);
        },
        'project_id' => function($value) use ($projects_ids) {
            return validate_project($value, $projects_ids);
        },
        'date_done' => function($value) {
            return is_date_valid($value);
        }
    ];

// определяем массив для хранения ошибок валидации формы
    $errors = [];

// сохраняем в массив данные из полей формы
    $task = filter_input_array(INPUT_POST, ['name' => FILTER_DEFAULT, 'project_id' => FILTER_DEFAULT,
        'date_done' => FILTER_DEFAULT], true);

// применяем правила валидации к каждому полю формы
    foreach ($task as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
    }

// очищаем массив ошибок от пустых значений
    $errors = array_filter($errors);
    return $errors;

}

/**
 * Проверяет валидность email
 * @param $value string Содержимое поля
 * @return string Если поле не является email, возвращает ошибку, иначе null
 */
function validate_email(string $value) : string | null {
    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
        return "Укажите E-mail в корректном формате";
    }

    return null;
}

/**
 * Валидирует поля формы регистрации
 *
 * @param mysqli $connection Объект с данными для подключения к базе
 * @param array $post Массив содержащий данные из полей формы
 * @return array массив с ошибками
 */
function validate_registration_form(mysqli $connection, array $post) : array {

    // определяем массив правил для проверки полей формы
    $rules = [
        'email' => function($value) {
            return validate_email($value);
        },
        'password' => function($value) {
            return validate_availability($value);
        },
        'name' => function($value) {
            return validate_availability($value);
        }
    ];

// определяем массив для хранения ошибок валидации формы
    $errors = [];

// сохраняем в массив данные из полей формы
    $user = filter_input_array(INPUT_POST, ['email' => FILTER_DEFAULT, 'password' => FILTER_DEFAULT,
        'name' => FILTER_DEFAULT], true);

// применяем правила валидации к каждому полю формы
    foreach ($user as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
    }

// проверяем наличие введеннго email в базе
    if (empty($errors)) {
        $errors['email'] = check_email_existance($connection, $user['email']);
    }

    // очищаем массив ошибок от пустых значений
    $errors = array_filter($errors);
    return $errors;

}

/**
 * Валидирует поля формы авторизации
 * @param mysqli $connection Объект с данными для подключения к базе
 * @param array $post Массив содержащий данные из полей формы
 * @return array массив с ошибками
 */
function validate_authorization_form(mysqli $connection, array $post) : array {

    // определяем массив правил для проверки полей формы
    $rules = [
        'email' => function($value) {
            return validate_email($value);
        },
        'password' => function($value) {
            return validate_availability($value);
        }
    ];

// определяем массив для хранения ошибок валидации формы
    $errors = [];

// сохраняем в массив данные из полей формы
    $user = filter_input_array(INPUT_POST, ['email' => FILTER_DEFAULT, 'password' => FILTER_DEFAULT], true);

// применяем правила валидации к каждому полю формы
    foreach ($user as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
    }

// проверяем наличие введеннго email в базе
    if (empty($errors)) {
        $errors['email'] = check_email_existance($connection, $user['email']);
    }

// очищаем массив ошибок от пустых значений
    $errors = array_filter($errors);
    return $errors;

}
