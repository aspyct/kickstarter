<?php
namespace Aspyct\Kickstarter\Shell;

interface Command {
    function run(array $args);
    function help(array $args);
    function getName();
    function getBrief();
}
