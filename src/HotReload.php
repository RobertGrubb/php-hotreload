<?php

foreach (glob(dirname(__FILE__) . '/HotReload/**/*.php') as $filename) {
    require_once $filename;
}
