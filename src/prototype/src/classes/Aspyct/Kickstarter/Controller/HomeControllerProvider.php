<?php
namespace Aspyct\Kickstarter\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;

class HomeControllerProvider implements ControllerProviderInterface {
    public function connect(Application $app) {
        $controllers = new ControllerCollection();
        
        $controllers->get('/', function (Application $app) {
            return $app['twig']->render('home/main.twig', array(
                'project' => '{{ project.name }}'
            ));
        });
        
        return $controllers;
    }
}
