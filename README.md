# Hot Reloading for PHP

This is a simple package that will allow you to live reload a script that would stay alive via a cli command. For example, if you have a infinite while loop running in a script and do not want to stop and restart every time you make a change, this package will work well for you.

# Credit

Thanks to the package `felippe-regazio/php-hot-reloader` for a great way to check for changes with a directory and files.

# Installing

```
composer require robert-grubb/php-hotreload
```

# Usage

```
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Instantiates new HotReloader and listens for changes on
 * the test.php script.
 */
$hotReloader = new HotReload\Instance(
    __DIR__ . '/test.php', // The entry file to be called for the daemon
    __DIR__, // The root directory to watch changes for
    ['.'], // The list of files or directories to watch changes for
    [] // The list of directories/files to ignore for watching
);

/**
 * Start the reload watcher
 */
$hotReloader->startWatcher();
```

# Preview

![](preview.png?raw=true)
