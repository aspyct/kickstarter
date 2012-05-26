<?php
require_once __DIR__.'/../src/classes/autoload.php';

$vendors = __DIR__.'/../src/vendor/autoload.php';
if (is_file($vendors)) {
    require_once $vendors;
}
