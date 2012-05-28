<?php
namespace Aspyct\Kickstarter\Shell;

class VersionCommand extends AbstractCommand {
    public function getBrief() {
        return 'Version is ' . VERSION;
    }

    public function getName() {
        return 'version';
    }

    public function help(array $args) {
        $this->out($this->getBrief());
    }

    public function run(array $args) {
        $this->out($this->getBrief());
    }
}
