<?php
session_start();
require_once 'functions/db.php';
require_once 'functions/template.php';
require_once 'functions/validate.php';

if (!file_exists(__DIR__ . '/config.php')) {
    exit ('Создайте файл config.php на основе файла config.sample.php и сконфигурируйте его');
}

$config = require __DIR__ . '/config.php';

// подключаемся к базе данных
$connection = db_connection($config['db']);
