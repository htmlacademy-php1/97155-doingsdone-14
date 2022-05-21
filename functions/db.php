<?php

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
 * Добавляет запись о новой задаче в базу
 * @param mysqli $connection Объект с данными для подключения
 * @param array $new_task Массив с данными добавляемой задачи
 * @param int $user_id ID пользователя
 * @return bool При успешном добавлении возвращает true
 */
function add_task(mysqli $connection, array $new_task, int $user_id) : bool {
    // подготовленное выражение для запроса на добавление новой задачи в базу
    $sql = "INSERT INTO tasks (name, project_id, date_done, user_id, file) VALUES (?, ?, ?, $user_id, ?)";
    $stmt = db_get_prepare_stmt($connection, $sql, $new_task);
    $result = mysqli_stmt_execute($stmt);
    return $result;
}

/**
 * Добавляет запись о новом пользователе в базу
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
 * Ищет в базе пользователя с переданным email
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

/**
 * Проверяет есть ли такой email в базе
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
 * Ищет задачи по фразе из формы поиска
 * @param mysqli $connection Объект с данными для подключения
 * @param string $search фраза введенная в форму поиска
 * @param int $user_id ID пользователя
 * @return array Если есть задачи с названием релевантным запросу возвращает массив, иначе null
 */
function get_search(mysqli $connection, string $search, int $user_id) : array | null {
    $sql = "SELECT * FROM tasks WHERE user_id = $user_id AND MATCH (name) AGAINST (?)";
    $stmt = db_get_prepare_stmt($connection, $sql, [$search]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);

    if ($tasks != false) {
        // переводим формат даты выполнения задачи к виду dd-mm-yyyy
        foreach ($tasks as &$task) {
            $date_done = date_convert($task['date_done']);
            $task['date_done'] = $date_done;
        }
        return $tasks;
    } else {
        return null;
    }
}
