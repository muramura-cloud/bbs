<?php

function mb_trim($string) {
    return preg_replace('/\A[\x00\s]++|[\x00\s]++\z/u', '', $string);
}

function is_empty($value) {
    return ($value === '' || $value === null || $value === []);
}

function get_inputs($request_parameter, $input_keys) {
    $inputs = [];
    foreach ($input_keys as $input_key) {
        $input              = isset($request_parameter[$input_key]) ? mb_trim($request_parameter[$input_key]) : null;
        $inputs[$input_key] = !is_empty($input) ? $input : null;
    }

    return $inputs;
}

function h($string, $flags = ENT_QUOTES, $encoding = 'UTF-8') {
    return htmlspecialchars($string, $flags, $encoding);
}
