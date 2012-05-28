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
use Aspyct\Kickstarter\Model\Project;

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

/**
 * Thanks to Composer for this function http://getcomposer.org
 */
function out($text, $color = null, $newLine = true)
{
    if (DIRECTORY_SEPARATOR == '\\') {
        $hasColorSupport = false !== getenv('ANSICON');
    } else {
        $hasColorSupport = true;
    }

    $styles = array(
        'success' => "\033[0;32m%s\033[0m",
        'error' => "\033[31;31m%s\033[0m",
        'info' => "\033[33;33m%s\033[0m"
    );

    $format = '%s';

    if (isset($styles[$color]) && $hasColorSupport) {
        $format = $styles[$color];
    }

    if ($newLine) {
        $format .= PHP_EOL;
    }

    printf($format, $text);
}

function confirm($message, $color=null) {
    out("$message (y/N) ", $color, false);
    $fd = fopen('php://stdin', 'r');
    $response = trim(fgets($fd));
    return $response == 'y';
}

// Define some classes and tasks...





class CommandGroup implements Command {
    /**
     * @var array <Command>
     */
    private $subcommands;
    
    private $name = '<cli>';
    
    public function help(array $args=array()) {
        if (empty($args)) {
            $this->globalHelp();
        }
        else {
            $this->specificHelp($args);
        }
    }
    
    private function globalHelp() {
        echo "Available commands:\n";
        
        foreach ($this->subcommands as $command) {
            printf("  %-14s%s\n", $command->getName(), $command->getBrief());
        }
    }
    
    private function specificHelp(array $args) {
        $command = array_shift($args);
        
        if (array_key_exists($command, $this->subcommands)) {
            $this->subcommands[$command]->help($args);
        }
        else {
            echo "No such command: $command\n";
            $this->globalHelp();
        }
    }
    
    public function run(array $args) {
        // $args[0] is the program name
        $this->name = array_shift($args);
        
        if (empty($args)) {
            $this->help();
        }
        else {
            $command = array_shift($args);
            
            if (array_key_exists($command, $this->subcommands)) {
                $this->subcommands[$command]->run($args);
            }
            else {
                echo "No such command: $command\n";
                $this->help();
            }
        }
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getBrief() {
        return 'The main CLI of this software.';
    }
    
    public function addSubCommand(Command $subcommand) {
        $this->subcommands[$subcommand->getName()] = $subcommand;
    }
}


class CommandGroupHelp implements Command {
    /**
     * @var CommandGroup
     */
    private $commandGroup;
    
    public function __construct(CommandGroup $commandGroup) {
        $this->commandGroup = $commandGroup;
    }
    
    public function getBrief() {
        return 'Need help about help ?';
    }
    
    public function getName() {
        return 'help';
    }
    
    public function help(array $args) {
        echo "Meta help rocks !\n";
    }
    
    public function run(array $args) {
        $this->commandGroup->help($args);
    }
}

// Set up the software
$cli = new CommandGroup();
$cli->addSubCommand(new CreateProjectCommand());
$cli->addSubCommand(new CommandGroupHelp($cli));
$cli->addSubCommand(new VersionCommand());

$cli->run($argv);

// This must remain the last line of the file.
__HALT_COMPILER();