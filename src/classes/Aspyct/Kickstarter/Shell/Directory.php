<?php
namespace Aspyct\Kickstarter\Shell;

interface Directory {
    /**
     * @param string $path path to test, relative to this FileSystem object 
     * @return boolean
     */
    function isFile($path);
    
    /**
     * @param string $path path to test, relative to this FileSystem object 
     * @return boolean
     */
    function isDirectory($path='.');
    
    /**
     * @param string $directory path to the directory to list
     * @return array list of files
     */
    function recursiveList($directory='.');
    
    /**
     * Copy a file from an absolute location to a location relative to this FileSystem object
     * @param string $from absolute path to the file to copy
     * @param string $to relative path to the new copy 
     */
    function copy($from, $to);
    
    /**
     * @param string $path relative path to the directory to be created
     */
    function mkdir($path='.', $perms=0755, $recursive=true);
    
    /**
     * Return an instance of the same class with a subdirectory
     * @return Directory 
     */
    function getSubdirectory($path);
}