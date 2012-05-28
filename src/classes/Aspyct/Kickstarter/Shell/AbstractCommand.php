<?php
namespace Aspyct\Kickstarter\Shell;

abstract class AbstractCommand implements Command {
    /**
     * @var Dialog
     */
    private $dialog;
    
    /**
     * @param Dialog $dialog 
     */
    public function __construct(Dialog $dialog) {
        $this->dialog = $dialog;
    }
    
    /**
     * Shortcut to $this->getDialog()->out()
     */
    protected function out($message, $color=null, $newline=true) {
        return $this->getDialog()->out($message, $color, $newline);
    }
    
    /**
     * Shortcut to $this->getDialog()->confirm()
     */
    protected function confirm($message) {
        return $this->getDialog()->confirm($message);
    }

    /**
     * @return Dialog
     */
    public function getDialog() {
        return $this->dialog;
    }

    /**
     * @param Dialog $dialog 
     */
    public function setDialog(Dialog $dialog) {
        $this->dialog = $dialog;
    }
}
