<?php
require_once __DIR__.'/../classes/autoload.php';

$vendors = __DIR__.'/../vendor/autoload.php';
if (is_file($vendors)) {
    require_once $vendors;
}
