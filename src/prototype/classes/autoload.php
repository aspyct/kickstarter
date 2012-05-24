<?php
spl_autoload_register(function ($className) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $fullpath = __DIR__.'/'.$path.'.php';
    
    if (is_file($fullpath)) {
        require_once $fullpath;
    }
});