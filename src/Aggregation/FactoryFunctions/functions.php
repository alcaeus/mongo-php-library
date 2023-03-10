<?php

/** @var DirectoryIterator $file */
foreach (new DirectoryIterator(__DIR__) as $file) {
    if ($file->isDot() || !$file->isFile()) {
        continue;
    }

    if ($file->getFilename() === 'functions.php') {
        continue;
    }

    if ($file->getExtension() !== 'php') {
        continue;
    }

    require_once $file->getPathname();
}
