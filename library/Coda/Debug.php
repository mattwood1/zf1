<?php
class Coda_Debug extends Zend_Debug
{
}

function _d()
{
    foreach (func_get_args() as $arg) {
        Zend_Debug::dump($arg);
    }
}

function _dexit()
{
    call_user_func_array('_d', func_get_args());
    exit;
}