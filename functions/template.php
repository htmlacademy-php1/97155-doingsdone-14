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
 * Возвращает значение поля формы
 * @param string $name Наименование поля для которого нужно вернуть значение
 * @return string содержимое поля формы
 */
function get_post_val(string $name) : string {
    return filter_input(INPUT_POST, $name);
}

/**
 * Переносит загруженный файл в папку uploads
 * @param array $files Массив с данными о загруженном файле
 * @return string | null Возвращает пусть к загруженному файлу или null если файл не был загружен
 */
function upload_file(array $files) : string | null {
    if ($files['file']['size'] != 0) {
        $file_name = $files['file']['name'];
        $file_path = __DIR__ . '/../uploads/';
        $file_url = '/uploads/' . $file_name;
        if (move_uploaded_file($files['file']['tmp_name'], $file_path . $file_name) === false) {
            exit('Ошибка при записи файла');
        }
    } else {
        $file_url = null;
    }
    return $file_url;
}
