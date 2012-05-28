#!/usr/bin/php
<?php

/*
 * Copyright 2012 Antoine d'Otreppe de Bouvette
 * Available under the terms of the MIT License.
 * 
 * The contents of the prototype/ subdirectory are in the public domain.
 * Want to get in touch ?
 * - a.dotreppe@aspyct.org
 * - @aspyct on twitter
 * 
 * Run this script in a shell to get some help
 */

use Aspyct\Kickstarter\Shell\Command;
use Aspyct\Kickstarter\Shell\CreateProjectCommand;
use Aspyct\Kickstarter\Shell\VersionCommand;
use Aspyct\Kickstarter\Shell\CommandGroupHelp;
use Aspyct\Kickstarter\Shell\CommandGroup;
use Aspyct\Kickstarter\Model\Project;
use Aspyct\Kickstarter\Shell\Dialog;

try {
    Phar::mapPhar();
    
    function path_to($fileInProject) {
        return sprintf('phar://%s/%s', __FILE__, $fileInProject);
    }
    // Running from Phar
}
catch (PharException $_) {
    function path_to($fileInProject) {
        return sprintf('%s/%s', __DIR__, $fileInProject);
    }
}


define('PROTOTYPE_DIR', path_to('prototype'));
define('RES_DIR', path_to('res'));
require_once path_to('vendor/Twig/Autoloader.php');
require_once path_to('classes/autoload.php');

DEFINE('VERSION', '0.1');


Twig_Autoloader::register();

// Set up the software
$dialog = new Dialog();
$cli = new CommandGroup($dialog);
$cli->addSubCommand(new CreateProjectCommand($dialog));
$cli->addSubCommand(new CommandGroupHelp($dialog, $cli));
$cli->addSubCommand(new VersionCommand($dialog));

$cli->run($argv);

// This must remain the last line of the file.
__HALT_COMPILER();