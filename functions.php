<?php

/**
 * Считает количество задач в проекте
 * @param mysqli $connection ОБъект с данными для подключения к базе
 * @param int $project_id ID проекта
 * @return int $tasks_count Количество задач в проекте
 */
function tasks_count (mysqli $connection, int $project_id) : int {
    $sql_tasks_count = "SELECT COUNT(id) FROM tasks WHERE project_id = $project_id AND user_id = 1";
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
 * @return array $projects Массив проектов пользователя
 */
function get_projects (mysqli $connection) : array {
    $sql_projects = "SELECT name, id FROM projects WHERE user_id = 1";
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
 * @return array $tasks Массив задач пользователя
 */
function get_tasks (mysqli $connection, int $project_id) : array {
    if ($project_id === 0) {
        $sql_projects = "SELECT name, date_done, done, file, project_id FROM tasks WHERE user_id = 1 ORDER BY dt_add DESC";
    } else {
        $sql_projects = "SELECT name, date_done, done, file, project_id FROM tasks WHERE user_id = 1 AND project_id = $project_id ORDER BY dt_add DESC";
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
 *
 * @param $id int ID проекта
 * @param $allowed_list array Массив существующих проектов
 *
 * @return string | null Если не находит проект, возвращает текст ошибки. Если находит, возвращает null
 */
function validate_category(int $id, array $allowed_list) : string | null {
    if (!in_array($id, $allowed_list)) {
        return "Указан несуществующий проект";
    }

    return null;
}

/**
 * Проверяет заполненность поля
 *
 * @param $value string | null Содержимое поля
 *
 * @return string Если поле пустое, возвращает ошибку, иначе null
 */
function validate_availability(string | null $value) : string | null {
    if (!$value) {
        return "Поле должно быть заполнено";
    }

    return null;
}

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * @param string $date Дата в виде строки
 *
 * @return null при совпадении с форматом 'ГГГГ-ММ-ДД', иначе string
 */
function is_date_valid(string $date) : bool {
    $format_to_check = 'Y-m-d';
    $date_time_obj = date_create_from_format($format_to_check, $date);
    if ($date_time_obj !== false && array_sum(date_get_last_errors()) === 0) {
        return null;
    }

    return "Укажите дату в формате ГГГГ-ММ-ДД";
}

?>
