<?php
/**
 * Configuration file for the development environment.
 * Here is the place to register services, controllers etc. into Silex.
 * This environment inherits from the prod environement.
 * 
 * If you are more than one working on this project, we highly encourage
 * you to create a local_<yourname> environement that subclasses this dev.
 */

use Silex\Application;

return function (Application $app) {
    // Turn on debug mode
    ini_set('display_errors', 'On');
    $app['debug'] = true;
    
    // Inherit from the prod environment
    $super = include __DIR__ . '/prod.php';
    $super($app);
    
    // Do custom initialization below
    
};
