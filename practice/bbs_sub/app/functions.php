<?php

require_once(__DIR__ . '/../config/env.php');

function db_connect() {
    $db_host = DB_HOST;
    $db_name = DB_NAME;

    $dbh = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8", DB_USER, DB_PASS, [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $dbh;
}

function mb_trim($string) {
    return preg_replace('/\A[\x00\s]++|[\x00\s]++\z/u', '', $string);
}

function is_empty($value) {
    return ($value === '' || $value === null || $value === []);
}

function get_inputs($request_parameter, $input_keys) {
    $inputs = [];
    foreach ($input_keys as $input_key) {
        if (isset($request_parameter[$input_key])) {
            $inputs[$input_key] = mb_trim($request_parameter[$input_key]);
        } else {
            $inputs[$input_key] = '';
        }
    }

    return $inputs;
}


function h($string, $flags = ENT_QUOTES, $encoding = 'UTF-8') {
    return htmlspecialchars($string, $flags, $encoding);
}
