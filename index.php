<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// подключаемся к базе данных
$connect = mysqli_connect("localhost", "phpuser", "phpuserpass", "doingsdone");
mysqli_set_charset($connect, "utf8");

// получаем список проектов для пользователя
$sql_projects = "SELECT name, id FROM projects WHERE user_id = 1";
$result_projects = mysqli_query($connect, $sql_projects);
$projects = mysqli_fetch_all($result_projects, MYSQLI_ASSOC);

// получаем список задач для пользователя
$sql_tasks = "SELECT name, date_done, done, file, project_id FROM tasks WHERE user_id = 1;";
$result_tasks = mysqli_query($connect, $sql_tasks);
$tasks = mysqli_fetch_all($result_tasks, MYSQLI_ASSOC);

// переводим формат даты выполнения задачи к виду dd-mm-yyyy
foreach ($tasks as &$task) {
    $date_done = date_convert($task['date_done']);
    $task['date_done'] = $date_done;
}

// выводим ошибку, если нет подключения к базе
if (!$result_projects || !$result_tasks ) {
    $error = mysqli_error($connect);
    print("Ошибка MySQL" . $error);
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

$page_content = include_template('main.php', ['projects' => $projects, 'tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks]);
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
print($layout_content);

?>
