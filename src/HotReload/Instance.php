<?php

namespace HotReload;

use \HotReload\DiffChecker;
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
     * @param instance DiffChecker
     */
    private $differ = false;

    /**
     * @param string hash
     * 
     * Stores the current hash of the directory/files to check against
     */
    private $hash = false;

    /**
     * @param string arguments
     * 
     * String of arguments to run entry file with
     */
    private $arguments = false;

    /**
     * @param bool outputLogsFromEntry
     * 
     * Whether the entry file logs are outputted via the HotReloader
     */
    private $outputLogsFromEntry = false;

    /**
     * Sets up the start time, entry file validation,
     * and then add all of the files of the directory 
     * to the files to check the modified time of.
     */
    public function __construct($options = [])
    {

        /**
         * Set default options
         */
        $defaultOptions = [
            'entryFile' => false,
            'entryArguments' => false,
            'rootDirectory' => false,
            'watch' => ['.'],
            'ignore' => [],
            'outputLogsFromEntry' => true
        ];

        /**
         * Merge the options passed with the defaults
         */
        $options = array_merge($defaultOptions, $options);

        /**
         * Check for root directory existence
         */
        if (!$options['rootDirectory']) {
            throw new \Exception('You must specify a root directory.');
        }

        /**
         * Check that watch is provided
         */
        if (!$options['watch']) {
            throw new \Exception('You must specify an array of directories/files to watch.');
        }

        $this->log('Configuring HotReloader...', 'info');

        /**
         * Sets the entry file that will be ran
         */
        $this->entryFile = $options['entryFile'];
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
         * Set arguments if passed
         */
        if ($options['entryArguments']) {
            $this->arguments = $options['entryArguments'];
        }

        /**
         * Set the logs output flag
         */
        $this->outputLogsFromEntry = $options['outputLogsFromEntry'];

        /**
         * Instantiate new differ
         */
        $this->differ = new DiffChecker([
            'ROOT'     => $options['rootDirectory'],
            'WATCH'    => $options['watch'],
            'IGNORE'   => $options['ignore']
        ]);
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
         * Set the hash
         */
        $this->hash = $this->differ->hash();

        /**
         * Start the loop for checking if files have
         * been modified
         */
        while ($this->watcherEnabled === true) {

            /**
             * Read the stdout from the popen process handler.
             */
            if ($this->proc) {
                if ($this->outputLogsFromEntry) {
                    if ($read = fread($this->proc, 2096)) {
                        $this->log('[' . basename($this->entryFile) . ']: ' . $read);
                        flush();
                    }
                }
            }

            /**
             * Get the current hash to compare to
             */
            $currentHash = $this->differ->hash();

            /**
             * Check if files have been modified. If so, stop the watcher,
             * then start it again.
             */
            if ($currentHash != $this->hash) {
                $this->stopWatcher();
                $this->log("HotReloader is now reloading " . $this->entryFile, 'warning');
                $this->startWatcher();
            }
        }
    }

    /**
     * This will stop the entryFile process
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
    }

    /**
     * Uses popen to open the script
     */
    private function runScript()
    {

        /**
         * Run the command for the entry php file
         */
        $command = 'php ' . $this->entryFile . ($this->arguments ? ' ' . $this->arguments : '');
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
}
