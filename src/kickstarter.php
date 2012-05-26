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
}

DEFINE('VERSION', '0.1');


Twig_Autoloader::register();

// Define some classes and tasks...
interface Command {
    function run(array $args);
    function help(array $args);
    function getName();
    function getBrief();
}

class Project {
    private $name;
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
}

class VersionCommand implements Command {
    public function getBrief() {
        return 'Version is ' . VERSION;
    }

    public function getName() {
        return 'version';
    }

    public function help(array $args) {
        echo $this->getBrief()."\n";
    }

    public function run(array $args) {
        echo $this->getBrief()."\n";
    }
}

class CreateProjectCommand implements Command {
    public function help(array $args=array()) {
        printf("Syntax: %s <project name> <directory = .>\n", $this->getName());
    }

    public function run(array $args) {
        if (empty($args)) {
            $this->help();
        }
        else {
            $project = new Project();
            $project->setName(array_shift($args));
            
            $target = empty($args) ? '.' : array_shift($args);
            if (is_dir($target)) {
                echo "Warning: target directory $target already exists.\n";
                echo "Override ? (y/n) ";
                $fd = fopen('php://stdin', 'r');
                $response = trim(fgets($fd));
                if ($response != 'y') {
                    echo "Exiting.\n";
                    return;
                }
            }
            else {
                mkdir($target, 0755, true);
            }
            
            $twiggable = json_decode(file_get_contents(RES_DIR.'/twiggable.json'));
            
            $twigLoader = new Twig_Loader_Filesystem(PROTOTYPE_DIR);
            $twig = new Twig_Environment($twigLoader);
            
            
            // Copy files and create directories
            foreach ($this->recursiveList(PROTOTYPE_DIR) as $file) {
                $source = PROTOTYPE_DIR.DIRECTORY_SEPARATOR.$file;
                $dest = $target.DIRECTORY_SEPARATOR.$file;
                
                if (in_array($file, $twiggable)) {
                    echo "Twig  $file\n";
                    $text = $twig->render($file, array(
                        'project' => $project
                    ));
                    file_put_contents($dest, $text);
                }
                else if (is_file($source)) {
                    echo "Copy  $file\n";
                    copy($source, $dest);
                }
                else if (is_dir($source) && !is_dir($dest)) {
                    echo "Mkdir $file\n";
                    mkdir($dest, 0755, true);
                }
            }
            
            // Run composer for the first time
            $origin = getcwd();
            chdir($target);
            
            $this->runScript("curl http://getcomposer.org/installer | php");
            
            if (!is_file('composer.phar')) {
                echo "Could not download composer.\n";
                return;
            }
            
            $this->runScript("chmod +x composer.phar");
            $this->runScript("./composer.phar install");
            
            chdir($origin);
        }
    }
    
    private function runScript($command) {
        echo $command."\n";
        system($command);
    }
    
    private function recursiveList($dir) {
        $entries = array();
        
        $it = new DirectoryIterator($dir);
        foreach ($it as $entry) {
            if ($entry->isFile()) {
                $entries[] = $entry->getFilename();
            }
            else if ($entry->isDir() && !$entry->isDot()) {
                $entries[] = $entry->getFilename();
                $entryName = $entry->getFilename();
                $fullpath = $dir.DIRECTORY_SEPARATOR.$entry->getFilename();
                foreach ($this->recursiveList($fullpath) as $path) {
                    $entries[] = $entryName.DIRECTORY_SEPARATOR.$path;
                }
            }
        }
        
        return $entries;
    }
    
    public function getName() {
        return 'create';
    }
    
    public function getBrief() {
        return 'Create a new project';
    }
}

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