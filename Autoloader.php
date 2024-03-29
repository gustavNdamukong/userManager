<?php

spl_autoload_register('autoloader');

function autoloader($className)
{
    // Try loading the file assuming the file path matches the namespace
    $namespaces = explode('\\', $className);
    $class = array_pop($namespaces);
    $fileName = __DIR__ . '/' . implode('/', $namespaces) . '/' . $class . '.php';
    if (file_exists($fileName)) {
        include_once($fileName);
    }
}
