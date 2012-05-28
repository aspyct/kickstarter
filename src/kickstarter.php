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
    
    // Running from Phar
    define('PROTOTYPE_DIR', 'phar://' . __FILE__ . '/prototype');
    define('RES_DIR', 'phar://' . __FILE__ . '/res');
    require_once 'phar://' . __FILE__ . '/vendor/Twig/Autoloader.php';
}
catch (PharException $_) {
    define('PROTOTYPE_DIR', __DIR__.'/prototype');
    define('RES_DIR', __DIR__.'/res');
    require_once __DIR__.'/vendor/Twig/Autoloader.php';
    require_once __DIR__.'/classes/autoload.php';
}

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