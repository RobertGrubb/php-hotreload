# Hot Reloading for PHP

This is a simple package that will allow you to live reload a script that would stay alive via a cli command (daemon). For example, if you have a infinite while loop running in a script and do not want to stop and restart every time you make a change, this package will work well for you.

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
 * Instantiates new HotReloader and listens for changes the directory
 * specified except for the ignored directory.
 */
$hotReloader = new HotReload\Instance([
    'entryFile' => __DIR__ . '/test.php', // Entry file to run command
    'entryArguments' => false, // String of arguments for entry (ex. --foo=bar)
    'rootDirectory' => __DIR__, // The root directory
    'watch' => [ '.' ], // List of files/directories to watch (Relative path to root)
    'ignore' => [ 'ignored' ] // List of files/directories to ignore (Relative path to root)
]);

/**
 * Start the reload watcher
 */
$hotReloader->startWatcher();
```

# Preview

![](preview.png?raw=true)
