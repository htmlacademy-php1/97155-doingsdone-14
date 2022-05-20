<?php
session_start();
require_once 'functions/db.php';
require_once 'functions/template.php';
require_once 'functions/validate.php';

// подключаемся к базе данных
$config = require_once 'config.php';
$connection = db_connection($config['db']);
