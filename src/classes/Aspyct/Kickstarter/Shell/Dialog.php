<?php

namespace Aspyct\Kickstarter\Shell;

class Dialog {
    /**
     * Thanks to Composer for this function http://getcomposer.org
     */
    public function out($text, $color = null, $newLine = true) {
        if (DIRECTORY_SEPARATOR == '\\') {
            $hasColorSupport = false !== getenv('ANSICON');
        }
        else {
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

    public function confirm($message, $color = null) {
        $this->out("$message (y/N) ", $color, false);
        $fd = fopen('php://stdin', 'r');
        $response = trim(fgets($fd));
        return $response == 'y';
    }
}
