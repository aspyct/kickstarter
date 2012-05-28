<?php
namespace Aspyct\Kickstarter\Shell;

class CommandGroupHelp extends AbstractCommand {
    /**
     * @var CommandGroup
     */
    private $commandGroup;
    
    public function __construct(Dialog $dialog, CommandGroup $commandGroup) {
        parent::__construct($dialog);
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
