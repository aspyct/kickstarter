<?php
namespace Aspyct\Kickstarter\Shell;

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
