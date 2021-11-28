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
//
/*
echo '<pre><br />';
echo '$method<br />';
var_dump($method);
echo '</pre><br />';
*/
// string(3) "GET"


// AV 20211119
// response(error_response(1002, 'Тестовая заглушка сработала...'));
/*
В запросе требуется: 
Указанный в ошибке параметр v
{"success":"false","error":{"error_code":1002,"error_msg":"Invalid request: v (version API) is required"}}

--
В хидере надо посылать 

KEY
v
VALUE
1
--
*/


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

//var_dump($url);

//var_dump($path);


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
/*
AV 20211120
Сейчас в хидере для запросов обязательны следующие: 
KEY - VALUE
v - 1 (Версия, всегда равна 1)
project - copybro (могут быть значения ['copybro', 'mafin'] см выше)
token - 1 (см в БД sessions.token, завели 1 запись и в этом поле поставили значение 1
--
Добавляем еще 1 требование. 
В запросе должен быть 

KEY
apiname

И значения могут быть 
user.get для GET /user.get
user.update для POST /user.update
notifications.get для GET /notifications.get
notifications.read для POST /notifications.read

--
*/
    // routes (users)
    // your methods here ...
$apiname = $headers['apiname'] ?? '';
if (!$apiname) {
    response(error_response(1002, 'Invalid request: apiname is required :: Недопустимый запрос, отсутствует apiname.'));
}
//
$apiname_allowed_ar = ['user.get', 'user.update', 'notifications.get', 'notifications.read'];
if (!in_array($apiname, $apiname_allowed_ar)) {
    response(error_response(1002, 'Invalid request: apiname ' . $apiname . ' is incorrect. Недопустимый запрос, название метода apiname ' . $apiname . ' является некорректным.'));
}
//
switch ($apiname) {
    case $apiname_allowed_ar[0]:
        // GET /user.get
        if ($method != 'GET') response(error_response(1002, 'Invalid method: ' . $method . ' for request ' . $apiname . '.'));
        // получаем 1-й параметр и значение из запроса...
        $key0 = array_keys($query_raw)[0];
        $val0 = array_values($query_raw)[0];
        $data = array($key0 => $val0);
        $rez1 = User::user_info($data);
        response($rez1);
        break;
    case $apiname_allowed_ar[1]:
        // POST /user.update
        if ($method != 'POST') response(error_response(1002, 'Invalid method: ' . $method . ' for request ' . $apiname . '.'));
        $data = $query_raw;
//* Если в запросе нет ни одного из полей - выводим ошибку
// `first_name`, `last_name`, `middle_name`, `email` и `phone`
        if ((!isset($data['first_name'])) 
        and (!isset($data['last_name'])) 
        and (!isset($data['middle_name'])) 
        and (!isset($data['email'])) 
        and (!isset($data['phone']))) 
//
        response(error_response(1002, 'No one field in request!'));
//* Если одного из полей нет в запросе - оно не обновляется
        if ((!isset($data['first_name'])) 
        or (!isset($data['last_name'])) 
        or (!isset($data['middle_name'])) 
        or (!isset($data['email'])) 
        or (!isset($data['phone']))) 
        response(error_response(1002, 'One or more of requered field is absent!'));
//
        $rez1 = User::user_update($data);
        // response("Request " . $apiname_allowed_ar[1] . " ok");
        response($rez1);
        break;
    case $apiname_allowed_ar[2]:
        // GET /notifications.get
        if ($method != 'GET') response(error_response(1002, 'Invalid method: ' . $method . ' for request ' . $apiname . '.'));
        $data = $query_raw;
        $rez1 = User::notifications_get($data);
        // response("Request " . $apiname_allowed_ar[2] . " ok");
        response($rez1);
        break;
    case $apiname_allowed_ar[3]:
        // POST /notifications.read
        if ($method != 'POST') response(error_response(1002, 'Invalid method: ' . $method . ' for request ' . $apiname . '.'));
        $data = $query_raw;
        $rez1 = User::notifications_read($data);
        //response("Request " . $apiname_allowed_ar[3] . " ok");
        response($rez1);
        break;
}
//
response("No info for this request. Нет информации по данному запросу.");
/*
{
    "success": "true",
    "response": "No info for this request. Нет информации по данному запросу."
}
*/
//
}
