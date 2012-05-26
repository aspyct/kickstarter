<?php
/**
 * Configuration file for the production environment.
 * Here is the place to register services, controllers etc. into Silex.
 * This environment will be inherited by the "dev" env. 
 */

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Aspyct\Kickstarter\Controller\HomeControllerProvider;

return function (Application $app) {
    // Be safe
    ini_set('display_errors', 'Off');
    
    $app->register(new TwigServiceProvider(), array(
        'twig.path' => __DIR__ . '/../views'
    ));
    
    $app->mount('', new HomeControllerProvider());
};
