<?php

/**
 * Возвращает массив проектов пользователя
 * @param object $connection Объект с данными для подключения к базе
 * @return array $projects Массив проектов пользователя
 */
function get_projects (object $connection) : array {
    $sql_projects = "SELECT name, id FROM projects WHERE user_id = 1";
    $result_projects = mysqli_query($connection, $sql_projects);
    if (!$result_projects) {
        $error = mysqli_error($connection);
        print("Ошибка MySQL" . $error);
    } else {
        $projects = mysqli_fetch_all($result_projects, MYSQLI_ASSOC);
    }
    return $projects;
}

/**
 * Возвращает массив задач пользователя
 * @param object $connection Объект с данными для подключения к базе
 * @return array $tasks Массив задач пользователя
 */
function get_tasks (object $connection) : array {
    $sql_projects = "SELECT name, date_done, done, file, project_id FROM tasks WHERE user_id = 1";
    $result_tasks = mysqli_query($connection, $sql_projects);
    if (!$result_tasks) {
        $error = mysqli_error($connection);
        print("Ошибка MySQL" . $error);
    } else {
        $tasks = mysqli_fetch_all($result_tasks, MYSQLI_ASSOC);
    }
    return $tasks;
}

/**
 * Считает количество задач в проекте
 * @param array $tasks Ассоциативный массив задач
 * @param int $project_id ID проекта
 * @return int $tasks_count Количество задач в проекте
 */
function tasks_count (array $tasks, int $project_id) : int {
    $tasks_count = 0;
    foreach ($tasks as $task) {
        settype($task['project_id'], "integer");
        if ($project_id === $task['project_id']) {
            $tasks_count++;
        }
    }
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

?>
