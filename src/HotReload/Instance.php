<?php

namespace HotReload;

use \Codedungeon\PHPCliColors\Color;

/**
 * Hot Reloading
 */
class Instance
{

    /**
     * @param string entryFile
     * 
     * This holds the entry file for the watcher
     */
    private $entryFile = false;

    /**
     * @param array files
     * 
     * This holds the list of files to check the modified
     * time against the start time.
     */
    private $files = [];

    /**
     * @param int startTime
     * 
     * A unix timestamp that will be used to check the modified
     * time of a file against
     */
    private $startTime = false;

    /**
     * @param handler proc
     * 
     * Will hold the result of the popen that is called for the 
     * entry file
     */
    private $proc = false;

    /**
     * @param bool watcherEnabled
     * 
     * The boolean that tells the while loop to run.
     */
    private $watcherEnabled = true;

    /**
     * Sets up the start time, entry file validation,
     * and then add all of the files of the directory 
     * to the files to check the modified time of.
     */
    public function __construct($entryFile, $directory = false, $extensions = "/^.*\.(php)$/")
    {
        $this->log('Configuring HotReloader...', 'info');

        /**
         * Sets the start time to now. Any changes after this
         * will be what triggers the reload for the entry file
         * and directory that is passed.
         */
        $this->startTime = time();

        /**
         * Sets the entry file that will be ran
         */
        $this->entryFile = $entryFile;

        $this->log('Entry file set to ' . $this->entryFile, 'info');

        /**
         * Validate that the entry file exists.
         */
        if (!file_exists($this->entryFile)) {
            throw new \Exception('Unable to find file ' . $this->entryFile);
        }

        $this->log('Entry file does exist...', 'info');

        /**
         * If no entry file is passed, throw an exception as it is required.
         */
        if (!$this->entryFile) {
            throw new \Exception('Unable to get entryFile');
        }

        /**
         * Add the entry file to the list of files to check
         */
        $this->files[] = $entryFile;

        /**
         * If a directory is passed, find the files with the specified
         * extensions and add to the files array as files to check the 
         * modified time against.
         */
        if ($directory) {
            $this->log('Scanning directory ' . $directory . ' for files with extension ' . $extensions, 'info');
            $this->files = array_merge($this->files, $this->rsearch($directory, $extensions));
        }

        $this->log('Files to watch:', 'info');
        $this->log(json_encode($this->files), 'info');
    }

    /**
     * This starts the process for the entry file,
     * then it will check in a while loop if those modified
     * times have changed.
     * 
     * If it has, it will stop the while loop, stop the process,
     * restart the process with a new start time and checks will
     * then be ran again.
     */
    public function startWatcher()
    {

        /**
         * Run the process for the entry file
         */
        $this->runScript($this->entryFile);
        $this->log("HotReloader is now running " . $this->entryFile, 'info');

        /**
         * Start the loop for checking if files have
         * been modified
         */
        while ($this->watcherEnabled === true) {

            /**
             * filemtime is cached, so we need to clear it
             * when the loop runs through.
             */
            clearstatcache();

            /**
             * Read the stdout from the popen process handler.
             */
            if ($this->proc) {
                if ($read = fread($this->proc, 2096)) {
                    $this->log('[' . basename($this->entryFile) . ']: ' . $read);
                    flush();
                }
            }

            /**
             * Check if files have been modified. If so, stop the watcher,
             * then start it again.
             */
            if ($this->filesHaveBeenModified()) {
                $this->stopWatcher();
                $this->log("HotReloader is now reloading " . $this->entryFile, 'warning');
                $this->startWatcher();
            }
        }
    }

    /**
     * This will stop the entryFile process, reset the 
     * startTime.
     */
    public function stopWatcher($stopWatcher = false)
    {
        /**
         * Closes the popen handler
         */
        if ($this->proc) {
            pclose($this->proc);
        }

        /**
         * Stop the while loop
         */
        if ($stopWatcher) {
            $this->watcherEnabled = false;
        }

        /**
         * Reset the start time to now
         */
        $this->startTime = time();
    }

    /**
     * Uses popen to open the script
     */
    private function runScript()
    {

        /**
         * Run the command for the entry php file
         */
        $command = 'php ' . $this->entryFile;
        $this->proc = popen($command, 'r');
    }

    /**
     * Generic logger for the cli
     */
    public function log($text, $type = 'normal')
    {
        if ($type == 'info') {
            echo Color::LIGHT_GREEN, "[HotReloader]: " . $text, Color::RESET, PHP_EOL;
        } elseif ($type == 'warning') {
            echo Color::LIGHT_RED, "[HotReloader]: " . $text, Color::RESET, PHP_EOL;
        } else {
            echo "[HotReloader]: " . $text, PHP_EOL;
        }
    }

    /**
     * Iterates over an array of files and checks
     * the modified date against the start time.
     * 
     * If any of them have changed, it will return true.
     */
    public function filesHaveBeenModified()
    {
        $modified = false;

        /**
         * Iterate through each file in the array and compare
         * the modified time to the watcher start time.
         */
        foreach ($this->files as $file) {
            if (filemtime($file) > $this->startTime) {
                $modified = true;
            }
        }

        return $modified;
    }

    /**
     * Recursive file finder for the directory 
     * passed in the constructor.
     */
    public function rsearch($folder, $pattern)
    {
        $dir = new \RecursiveDirectoryIterator($folder);
        $ite = new \RecursiveIteratorIterator($dir);
        $files = new \RegexIterator($ite, $pattern, \RegexIterator::GET_MATCH);
        $fileList = array();
        foreach ($files as $file) {
            $fileList[] = $file[0];
        }
        return $fileList;
    }
}
