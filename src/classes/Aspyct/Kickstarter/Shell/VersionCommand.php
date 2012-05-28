<?php
namespace Aspyct\Kickstarter\Shell;

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
