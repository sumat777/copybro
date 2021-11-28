<?php

// INIT

require('./../cfg/general.inc.php');
require('./../includes/core/functions.php');

class_autoload(true);

DB::connect();
HTML::$compile_dir = '../'.HTML::$compile_dir;

// URL

// vars

$result = [];
$query = [];
$path = '';
$method = $_SERVER['REQUEST_METHOD'];

// headers

$headers = getallheaders();
$project = $headers['project'] ?? '';
$token = $headers['token'] ?? '';
$v = $headers['v'] ?? 0;

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// path

$url = $_SERVER['REQUEST_URI'];
$url = preg_replace('~^/api/~i', '', $url);
$url = explode('?', $url);
$path = isset($url[0]) && $url[0] ? flt_input($url[0]) : '';

// query

if ($method == 'GET') isset($url[1]) ? parse_str($url[1], $query_raw) : $query_raw = [];
else $query_raw = json_decode(file_get_contents('php://input'), true);
if (!$query_raw && $_POST) $query_raw = $_POST;
if (is_array($query_raw)) foreach ($query_raw as $key => $value) $query[flt_input($key)] = flt_input($value);

// ROUTES

error_log($method);
error_log($path);
error_log($token);
error_log(json_encode($query, JSON_UNESCAPED_UNICODE));

// validate

if (!$v) response(error_response(1002, 'Invalid request: v (version API) is required'));
else if ($v != 1) response(error_response(1002, 'Invalid request: v (version API) is incorrect'));

if (!$project) response(error_response(1002, 'Invalid request: project is required'));
else if (!in_array($project, ['copybro', 'mafin'])) response(error_response(1002, 'Invalid request: project is incorrect'));

// routes

if ($path == 'auth.sendCode') call('POST', $method, $query, 'Session::phone_code');
else if ($path == 'auth.confirmCode') call('POST', $method, $query, 'Session::phone_confirm');
else {
    // validate
    if (!$token) response(error_response(1001, 'User authorization failed: no access token passed.'));
    // session
    $response = Session::init(2, ['token' => $token]);
    if (isset($response['error_code'])) response($response);
    // routes (auth)
    if ($path == 'auth.logout') call('POST', $method, NULL, 'Session::logout');
    // routes (users)
    // your methods here ...
    // routes (not found)
    response(error_response(1002, 'Application authorization failed: method is unavailable with service token.'));
}
