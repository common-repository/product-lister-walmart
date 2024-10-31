<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 9/4/19
 * Time: 4:07 PM
 */
spl_autoload_register('walmartAutoLoader');

function walmartAutoLoader($class_name)
{
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    if (substr($class_name, 0, strlen('phpseclib')) === 'phpseclib')
    {
        include __DIR__ . DIRECTORY_SEPARATOR . 'src/Walmart' . DIRECTORY_SEPARATOR . $class_name . '.php';
    }else{
        include __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class_name . '.php';
    }

}