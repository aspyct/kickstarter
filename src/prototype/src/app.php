<?php
use Silex\Application;

// Include dependencies
$autoloaders = array('/classes/autoload.php', '/vendor/autoload.php');
foreach ($autoloaders as $autoloader) {
    $fullpath = __DIR__.$autoloader;
    
    if (is_file($fullpath)) {
        require_once $fullpath;
    }
}


// Retrieve the environement in the $_SERVER vars
// The default 'prod' is a safe choice in case we forget to set the var
// See the SetEnv directive in the .htaccess
$env = array_get($_SERVER, 'ENVIRONMENT', 'prod');

$envFile = __DIR__."/env/$env.php";
if (!is_file($envFile)) {
    die("Could not find env config for '$env'");
}
$config = include $envFile;

// Create and configure the application
$app = new Silex\Application();
$config($app);

return $app;
