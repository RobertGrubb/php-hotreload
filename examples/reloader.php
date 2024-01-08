<?php

/**
 * Hot Reloader for development. If files are changed, whether 
 * it be the entry file, or the directory specified, the bot 
 * will reload automatically so you don't have to keep starting, stopping,
 * and restarting the script every time a change is made.
 */

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Instantiates new HotReloader and listens for changes on 
 * the test.php script.
 */
$hotReloader = new HotReload\Instance(__DIR__ . '/test.php', __DIR__ . '/TestDirectory');

/**
 * Start the reload watcher
 */
$hotReloader->startWatcher();
