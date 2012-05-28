<?php

namespace Aspyct\Kickstarter\Shell;

class DefaultDirectory implements Directory {
    private $path;
    
    public function __construct($path) {
        $this->path = $path;
    }
    
    public function copy($from, $to) {
        return copy($from, $this->normalizePath($to));
    }

    public function isDirectory($path='.') {
        return is_dir($this->normalizePath($path));
    }

    public function isFile($path) {
        return is_file($this->normalizePath($path));
    }

    public function mkdir($path='.', $perms=0755, $recursive=true) {
        return mkdir($this->normalizePath($path), $perms, $recursive);
    }

    public function recursiveList($dir='.') {
        $dir = $this->normalizePath($dir);
        $entries = array();
        
        $it = new \DirectoryIterator($dir);
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

    public function getSubdirectory($path) {
        return new DefaultDirectory($this->normalizePath($path));
    }
    
    private function normalizePath($path) {
        // TODO Throw an exception if the path goes in the parent directory
        if ($path === '.') {
            return $this->getPath();
        }
        else {
            return $this->getPath().DIRECTORY_SEPARATOR.$path;
        }
    }
    
    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }
}
