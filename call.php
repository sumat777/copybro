<?php

// INIT

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') echo '';

require('./cfg/general.inc.php');
require('./includes/core/functions.php');

spl_autoload_register(function ($class_name) {
    include './includes/core/class_'.strtolower($class_name).'.php';
});

$includes_dir = opendir('./includes/controllers_call');
while (($inc_file = readdir($includes_dir)) != false)
    if (strstr($inc_file, '.php')) require('./includes/controllers_call/'.$inc_file);

// VARS

$location = isset($_POST['location']) ? flt_input($_POST['location']) : NULL;
$data = isset($_POST['data']) ? flt_input($_POST['data']) : NULL;

$dpt = isset($location['dpt']) ? $location['dpt'] : NULL;
$act = isset($location['act']) ? $location['act'] : NULL;

// SESSION

Session::init(1);

// ROUTE

Route::route_call($dpt, $act, $data);
