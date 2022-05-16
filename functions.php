<?php

/**
 * Считает количество задач в проекте
 * @param mysqli $connection ОБъект с данными для подключения к базе
 * @param int $project_id ID проекта
 * @param int $user_id ID пользователя
 * @return int $tasks_count Количество задач в проекте
 */
function tasks_count (mysqli $connection, int $project_id, int $user_id) : int {
    $sql_tasks_count = "SELECT COUNT(id) FROM tasks WHERE project_id = $project_id AND user_id = $user_id";
    $result_tasks_count = mysqli_query($connection, $sql_tasks_count);
    $tasks_count_array = mysqli_fetch_all($result_tasks_count, MYSQLI_ASSOC);
    $tasks_count = (int)$tasks_count_array[0]['COUNT(id)'];
    return $tasks_count;
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template($name, array $data = []) {
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * Определяет задачи до даты выполнения которых осталось менее 24 часов
 * @param string $task_date Дата выполнения задачи. Если у задачи дата указана, то получает строку, если дата не указана получает null
 * @return bool Если true, значит до даты выполнения менее 24 часов
 */
function task_important (?string $task_date) : bool {
    if (is_null($task_date)) {
        return false;
    } else {
        $date_timestamp = strtotime($task_date);
        $current_date = strtotime(date("d.m.Y h:i:s"));
        $hours_left = floor(($date_timestamp - $current_date) / 3600);
        if ($hours_left <= 24) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * Преобразовывает формат даты к виду dd-mm-yyyy
 * @param string | null $date Дата для преобразования
 * @return string | null Если было передано null, то вернет null, если было передано string, вернет string
 */
function date_convert (string | null $date) : string | null {
    if (is_null($date)) {
        return null;
    } else {
        $date_timestamp = strtotime($date);
        $date_newformat = date('d-m-Y', $date_timestamp);
    } return $date_newformat;
}

/**
 * Возвращает массив проектов пользователя
 * @param mysqli $connection Объект с данными для подключения к базе
 * @param int $user_id ID пользователя
 * @return array $projects Массив проектов пользователя
 */
function get_projects (mysqli $connection, int $user_id) : array {
    $sql_projects = "SELECT name, id FROM projects WHERE user_id = $user_id";
    $result_projects = mysqli_query($connection, $sql_projects);
    $projects = mysqli_fetch_all($result_projects, MYSQLI_ASSOC);
    foreach ($projects as &$project) {
        settype($project['id'], "integer");
    }
    return $projects;
}

/**
 * Возвращает массив задач пользователя
 * @param mysqli $connection Объект с данными для подключения к базе
 * @param int $project_id ID проекта
 * @param int $user_id ID пользователя
 * @return array $tasks Массив задач пользователя
 */
function get_tasks (mysqli $connection, int $project_id, int $user_id) : array {
    if ($project_id === 0) {
        $sql_projects = "SELECT name, date_done, done, file, project_id FROM tasks WHERE user_id = $user_id ORDER BY dt_add DESC";
    } else {
        $sql_projects = "SELECT name, date_done, done, file, project_id FROM tasks WHERE user_id = $user_id AND project_id = $project_id ORDER BY dt_add DESC";
    }
    $result_tasks = mysqli_query($connection, $sql_projects);
    $tasks = mysqli_fetch_all($result_tasks, MYSQLI_ASSOC);
    return $tasks;
}

/**
 * Подключается к базе данных
 * @param array $db Массив содержащий данные для подключения к базе
 * @return mysqli Объект содержащий ресурс соединения с базой
 */
function db_connection (array $db) : mysqli {
    $connection = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);
    if ($connection === false) {
        exit("Ошибка при подключении к БД");
    } else {
        mysqli_set_charset($connection, "utf8");
        return $connection;
    }
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = []) {
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

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
 * Возвращает значение поля формы
 * @param string $name Наименование поля для которого нужно вернуть значение
 * @return string содержимое поля формы
 */
function get_post_val(string $name) : string {
    return filter_input(INPUT_POST, $name);
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
 * Переносит загруженный файл в папку uploads
 *
 * @param array $files Массив с данными о загруженном файле
 *
 * @return string | null Возвращает пусть к загруженному файлу или null если файл не был загружен
 */
function upload_file(array $files) : string | null {
    if ($files['file']['size'] != 0) {
        $file_name = $files['file']['name'];
        $file_path = __DIR__ . '/uploads/';
        $file_url = '/uploads/' . $file_name;
        if (move_uploaded_file($files['file']['tmp_name'], $file_path . $file_name) === false) {
            exit('Ошибка при записи файла');
        }
    } else {
        $file_url = null;
    }
    return $file_url;
}

/**
 * Добавляет запись о новой задаче в базу
 *
 * @param mysqli $connection Объект с данными для подключения
 * @param array $new_task Массив с данными добавляемой задачи
 * @return bool При успешном добавлении возвращает true
 */
function add_task(mysqli $connection, array $new_task) : bool {
    // подготовленное выражение для запроса на добавление новой задачи в базу
    $sql = "INSERT INTO tasks (name, project_id, date_done, user_id, file) VALUES (?, ?, ?, 1, ?)";
    $stmt = db_get_prepare_stmt($connection, $sql, $new_task);
    $result = mysqli_stmt_execute($stmt);
    return $result;
}

/**
 * Проверяет валидность email
 *
 * @param $value string Содержимое поля
 *
 * @return string Если поле не является email, возвращает ошибку, иначе null
 */
function validate_email(string $value) : string | null {
    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
        return "Укажите E-mail в корректном формате";
    }

    return null;
}

/**
 * Проверяет есть ли такой email в базе
 *
 * @param mysqli $connection Объект с данными для подключения
 * @param string $email Email указаный пользователем
 * @return string Если такой email существует в базе, возвращает ошибку, иначе null
 */
function check_email_existance(mysqli $connection, string $email) : string | null {
    $email = mysqli_real_escape_string($connection, $email);
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) > 0) {
        return "Пользователь с этим email уже зарегистрирован";
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
 * Добавляет запись о новом пользователе в базу
 *
 * @param mysqli $connection Объект с данными для подключения
 * @param array $new_user Массив с данными добавляемого пользователя
 * @return bool При успешном добавлении возвращает true
 */
function add_user(mysqli $connection, array $new_user) : bool {
    $new_user['password'] = password_hash($new_user['password'], PASSWORD_DEFAULT);
    // подготовленное выражение для запроса на добавление нового пользователя в базу
    $sql = "INSERT INTO users (email, password, name) VALUES (?, ?, ?)";
    $stmt = db_get_prepare_stmt($connection, $sql, $new_user);
    $result = mysqli_stmt_execute($stmt);
    return $result;
}

/**
 * Валидирует поля формы авторизации
 *
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

/**
 * Ищет в базе пользователя с переданным email
 *
 * @param mysqli $connection Объект с данными для подключения
 * @param string $email Email указаный пользователем
 * @return array Если найдена запись с переданным email возвращает массив, иначе null
 */
function find_user(mysqli $connection, string $email) : array | null {
    $email = mysqli_real_escape_string($connection, $email);
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($connection, $sql);
    $user_data = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if ($user_data != false) {
        return $user_data;
    } else {
        return null;
    }
}

