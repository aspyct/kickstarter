<?php
namespace Aspyct\Kickstarter\Shell;

class CreateProjectCommand implements Command {
    /**
     *
     * @var \Twig_Environment
     */
    private $twig;
    
    /**
     *
     * @var array of files that must be passed through twig
     */
    private $twiggable;
    
    /**
     *
     * @var array of files to be ignored if they already exist in the target dir
     */
    private $ignoreIfExists;
    
    /**
     *
     * @var string target installation directory
     */
    private $target;
    
    public function __construct() {
        $twigLoader = new \Twig_Loader_Filesystem(PROTOTYPE_DIR);
        $this->twig = new \Twig_Environment($twigLoader);
        
        $rawTwiggable = file_get_contents(RES_DIR.'/twiggable.json');
        $this->twiggable = json_decode($rawTwiggable);
        
        $this->ignoreIfExists;
    }
    
    public function help(array $args=array()) {
        out("Syntax: {$this->getName()} <project name> <directory = .>");
    }

    public function run(array $args) {
        if (empty($args)) {
            $this->help();
        }
        else {
            $project = new Project();
            $project->setName(array_shift($args));
            
            // TODO Get the target from the options (-d --directory)
            $target = empty($args) ? $project->getName() : array_shift($args);
            if (!$this->confirmTarget($target)) {
                out("Exiting.", 'error');
                return;
            }
            
            
            
            
            
            // Copy files and create directories
            foreach ($this->recursiveList(PROTOTYPE_DIR) as $file) {
                $source = PROTOTYPE_DIR.DIRECTORY_SEPARATOR.$file;
                $dest = $target.DIRECTORY_SEPARATOR.$file;
                
                if (is_file($dest) && in_array($file, $this->ignoreIfExists)) {
                    continue;
                }
                
                if (in_array($file, $this->twiggable)) {
                    $text = $this->twig->render($file, array(
                        'project' => $project
                    ));
                    
                    if (file_put_contents($dest, $text) !== false) {
                        out('+f ', 'success', false);
                        out($file);
                    }
                    else {
                        out("Could not create file $file", 'error');
                    }
                }
                else if (is_file($source)) {
                    if (copy($source, $dest)) {
                        out('+f ', 'success', false);
                        out($file);
                    }
                    else {
                        out("Could not create $file", 'error');
                    }
                }
                else if (is_dir($source) && !is_dir($dest)) {
                    if (mkdir($dest, 0755, true)) {
                        out('+d ', 'success', false);
                        out($file);
                    }
                    else {
                        out("Could not create directory $file", 'error');
                    }
                }
            }
            
            // Run composer for the first time
            $origin = getcwd();
            chdir("$target/src");
            
            $this->runScript("curl http://getcomposer.org/installer | php");
            
            if (!is_file('composer.phar')) {
                echo "Could not download composer.\n";
                return;
            }
            
            $this->runScript("chmod +x composer.phar");
            $this->runScript("./composer.phar install");
            
            chdir($origin);
        }
    }
    
    /**
     *
     * @param string $path 
     * @post the target directory has been created if necessary
     * @post some files have been added to the ignore list if necessary
     * @return true if the target is confirmed, false to abort
     */
    private function confirmTarget($target) {
        if (is_dir($target)) {
            out("Warning: target directory $target already exists.", 'info');

            if (confirm('Overwrite ?')) {
                
                // Keep the .htaccess file ?
                if (!confirm('Overwrite .htaccess as well ?')) {
                    $this->ignoreIfExists[] = 'src/.htaccess';
                }
                
                return true;
            }
            else {
                return false;
            }
        }
        else {
            mkdir($target, 0755, true);
            return true;
        }
    }
    
    private function runScript($command) {
        echo $command."\n";
        system($command);
    }
    
    private function recursiveList($dir) {
        $entries = array();
        
        $it = new DirectoryIterator($dir);
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
    
    public function getName() {
        return 'create';
    }
    
    public function getBrief() {
        return 'Create a new project';
    }
}
