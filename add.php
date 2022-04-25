<?php
require_once 'functions.php';

// подключаемся к базе данных
$config = require_once 'config.php';
$connection = db_connection($config['db']);

// получаем список проектов для пользователя
$projects = get_projects($connection);

$page_content = include_template('add.php', ['projects' => $projects, 'connection' => $connection]);

$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);
print($layout_content);

?>
