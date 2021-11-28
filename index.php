<?php

// INIT

require('./cfg/general.inc.php');
require('./includes/core/functions.php');

spl_autoload_register(function ($class_name) {
    require './includes/core/class_'.strtolower($class_name).'.php';
});

$includes_dir = opendir('./includes/controllers_common');
while (($inc_file = readdir($includes_dir)) != false)
    if (strstr($inc_file, '.php')) require('./includes/controllers_common/'.$inc_file);

// GENERAL

Session::init();
Route::init();

$g['path'] = Route::$path;
$g['year'] = date('Y');

// OUTPUT

HTML::assign('global', $g);
HTML::display('./partials/index.html');
