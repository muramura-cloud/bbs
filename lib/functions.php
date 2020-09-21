<?php

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
        if (isset($request_parameter[$input_key]) && !is_empty(mb_trim($request_parameter[$input_key]))) {
            $inputs[$input_key] = mb_trim($request_parameter[$input_key]);
        } else {
            $inputs[$input_key] = null;
        }
    }

    return $inputs;
}

function validate_required($input, $input_name, $is_required) {
    if ($is_required && is_empty($input)) {
        return $input_name . 'を入力してください。';
    }

    return '';
}

function validate_length($input, $input_name, $length) {
    if (is_empty($input)) {
        return '';
    }

    $min = null;
    $max = null;
    if (isset($length['min'])) {
        $min = $length['min'];
    }
    if (isset($length['max'])) {
        $max = $length['max'];
    }

    if ($min && $max) {
        if (mb_strlen($input) < $min || mb_strlen($input) > $max) {
            return $input_name . 'は' . $min . '文字以上' . $max . '文字以下で入力してください。';
        }
    } elseif ($min) {
        if (mb_strlen($input) < $min) {
            return $input_name . 'は' . $min . '文字以上で入力してください。';
        }
    } elseif ($max) {
        if (mb_strlen($input) > $max) {
            return $input_name . 'は' . $max . '文字以下で入力してください。';
        }
    }

    return '';
}

function validate_pattern($input, $input_name, $pattern) {
    if (is_empty($input)) {
        return '';
    }

    if (!preg_match($pattern['regex'], $input)) {
        return $input_name . 'は' . $pattern['meaning'] . 'で入力してください。';
    }

    return '';
}


function validate($inputs, $validate_rules) {
    $error_messages = [];
    foreach ($validate_rules as $input_key => $validate_rule) {
        $input = null;
        if (isset($inputs[$input_key])) {
            $input = $inputs[$input_key];
        }

        foreach ($validate_rule['rules'] as $rule_name => $rule) {
            if ($rule_name === 'required') {
                $validate_required_error_message = validate_required($input, $validate_rule['name'], $rule);
                if ($validate_required_error_message !== '') {
                    $error_messages[] = $validate_required_error_message;
                }
            }

            if ($rule_name === 'length') {
                $validate_length_error_message = validate_length($input, $validate_rule['name'], $rule);
                if ($validate_length_error_message !== '') {
                    $error_messages[] = $validate_length_error_message;
                }
            }

            if ($rule_name === 'pattern') {
                $validate_pattern_error_message = validate_pattern($input, $validate_rule['name'], $rule);
                if ($validate_pattern_error_message !== '') {
                    $error_messages[] = $validate_pattern_error_message;
                }
            }
        }
    }

    return $error_messages;
}

function h($string, $flags = ENT_QUOTES, $encoding = 'UTF-8') {
    return htmlspecialchars($string, $flags, $encoding);
}
