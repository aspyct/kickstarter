<?php
namespace Aspyct\Kickstarter\Model;

class Project {
    private $name;
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
}
