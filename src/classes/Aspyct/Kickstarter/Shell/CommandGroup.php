<?php
namespace Aspyct\Kickstarter\Shell;

class CommandGroup extends AbstractCommand {
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
