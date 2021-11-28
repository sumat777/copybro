<?php

function controller_home() {
    return HTML::main_content('./partials/home.html', Session::$mode);
}