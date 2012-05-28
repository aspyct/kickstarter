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
        out($this->getBrief());
    }

    public function run(array $args) {
        out($this->getBrief());
    }
}

class CreateProjectCommand implements Command {
    /**
     *
     * @var Twig_Environment
     */
    private $twig;
    
    /**
     *
     * @var array of files that must be passed through twig
     */
    private $twiggable;
    
    /**
     *
     * @var array of files to be ignored if they already exist in the target dir
     */
    private $ignoreIfExists;
    
    /**
     *
     * @var string target installation directory
     */
    private $target;
    
    public function __construct() {
        $twigLoader = new Twig_Loader_Filesystem(PROTOTYPE_DIR);
        $this->twig = new Twig_Environment($twigLoader);
        
        $rawTwiggable = file_get_contents(RES_DIR.'/twiggable.json');
        $this->twiggable = json_decode($rawTwiggable);
        
        $this->ignoreIfExists;
    }
    
    public function help(array $args=array()) {
        out("Syntax: {$this->getName()} <project name> <directory = .>");
    }

    public function run(array $args) {
        if (empty($args)) {
            $this->help();
        }
        else {
            $project = new Project();
            $project->setName(array_shift($args));
            
            // TODO Get the target from the options (-d --directory)
            $target = empty($args) ? $project->getName() : array_shift($args);
            if (!$this->confirmTarget($target)) {
                out("Exiting.", 'error');
                return;
            }
            
            
            
            
            
            // Copy files and create directories
            foreach ($this->recursiveList(PROTOTYPE_DIR) as $file) {
                $source = PROTOTYPE_DIR.DIRECTORY_SEPARATOR.$file;
                $dest = $target.DIRECTORY_SEPARATOR.$file;
                
                if (is_file($dest) && in_array($file, $this->ignoreIfExists)) {
                    continue;
                }
                
                if (in_array($file, $this->twiggable)) {
                    $text = $this->twig->render($file, array(
                        'project' => $project
                    ));
                    
                    if (file_put_contents($dest, $text) !== false) {
                        out('+f ', 'success', false);
                        out($file);
                    }
                    else {
                        out("Could not create file $file", 'error');
                    }
                }
                else if (is_file($source)) {
                    if (copy($source, $dest)) {
                        out('+f ', 'success', false);
                        out($file);
                    }
                    else {
                        out("Could not create $file", 'error');
                    }
                }
                else if (is_dir($source) && !is_dir($dest)) {
                    if (mkdir($dest, 0755, true)) {
                        out('+d ', 'success', false);
                        out($file);
                    }
                    else {
                        out("Could not create directory $file", 'error');
                    }
                }
            }
            
            // Run composer for the first time
            $origin = getcwd();
            chdir("$target/src");
            
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
    
    /**
     *
     * @param string $path 
     * @post the target directory has been created if necessary
     * @post some files have been added to the ignore list if necessary
     * @return true if the target is confirmed, false to abort
     */
    private function confirmTarget($target) {
        if (is_dir($target)) {
            out("Warning: target directory $target already exists.", 'info');

            if (confirm('Overwrite ?')) {
                
                // Keep the .htaccess file ?
                if (!confirm('Overwrite .htaccess as well ?')) {
                    $this->ignoreIfExists[] = 'src/.htaccess';
                }
                
                return true;
            }
            else {
                return false;
            }
        }
        else {
            mkdir($target, 0755, true);
            return true;
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