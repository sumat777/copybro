<?php

function class_autoload($api = false) {
    if ($api) {
        spl_autoload_register(function($class_name) {
            require('./../includes/core/class_'.strtolower($class_name).'.php');
        });
    } else {
        spl_autoload_register(function($class_name) {
            require('./includes/core/class_'.strtolower($class_name).'.php');
        });
    }
}

function flt_input($var) {
    return str_replace(['\\', "\0", "'", '"', "\x1a", "\x00"], ['\\\\', '\\0', "\\'", '\\"', '\\Z', '\\Z'], $var);
}

function generate_rand_str($length, $type = 'hexadecimal') {
    // vars
    $str = '';
    if ($type == 'decimal') $chars = '0123456789';
    else if ($type == 'password') $chars = ['0123456789', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'];
    else $chars = 'abcdef0123456789';
    // generate
    for ($i = 0; $i < $length; $i++) {
        $microtime = round(microtime(true));
        if ($type != 'password') {
            srand($microtime + $i);
            $size = strlen($chars);
            $str .= $chars[rand(0, $size-1)];
        } else {
            $l = rand(-3, -1);
            $sub = substr($str, $l);
            if (!preg_match('~[0-9]~', $sub)) $chars_a = $chars[0];
            else if (!preg_match('~[A-Z]~', $sub)) $chars_a = $chars[1];
            else $chars_a = $chars[2];
            srand($microtime + $i);
            $size = strlen($chars_a);
            $str .= $chars_a[rand(0, $size-1)];
        }
    }
    // output
    return $str;
}

function error_response($code, $msg, $data = []) {
    $http_code = 400;
    header($_SERVER['SERVER_PROTOCOL'].' '.$http_code.' Error', true, $http_code);
    $result['error_code'] = $code;
    $result['error_msg'] = $msg;
    if ($data) $result['error_data'] = $data;
    return $result;
}

function response($response) {
    $response = !isset($response['error_code']) ? ['success'=>'true', 'response'=>$response] : ['success'=>'false', 'error'=>$response];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

function call($method_allow, $method_use, $data, $callback) {
    if ($method_allow != $method_use) response(error_response(1003, 'Application authorization failed: HTTP method is not supported.'));
    $data ? response($callback($data)) : response($callback());
}

function phone_formatting($phone) {
    if (preg_match('~^[78][\d]{10}$~', $phone)) $phone = preg_replace('~^([78])([\d]{3})([\d]{3})([\d]{2})([\d]{2})$~', '+$1 ($2) $3-$4-$5', $phone);
    return $phone;
}
