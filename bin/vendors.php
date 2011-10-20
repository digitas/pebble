#!/usr/bin/env php
<?php

$rootDir = dirname(dirname(__FILE__));
$vendorDir = $rootDir.'/vendor';

array_shift($argv);
if (!isset($argv[0])) {
    exit(<<<EOF
Digitas vendors script management.

Specify a command to run:

 install: install vendors as specified in deps or deps.lock (recommended)
 update:  update vendors to their latest versions (as specified in deps)


EOF
    );
}

if (!in_array($command = array_shift($argv), array('install', 'update'))) {
    exit(sprintf("Command \"%s\" does not exist.\n", $command));
}

if (!is_dir($vendorDir)) {
    mkdir($vendorDir, 0777, true);
}

// versions
$versions = array();
if ('install' === $command && file_exists($rootDir.'/deps.lock')) {
    foreach (file($rootDir.'/deps.lock', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $parts = array_values(array_filter(explode(' ', $line)));
        if (2 !== count($parts)) {
            exit(sprintf('The deps version file is not valid (near "%s")', $line));
        }
        $versions[$parts[0]] = $parts[1];
    }
}

$newversions = array();
$deps = parse_ini_file($rootDir.'/deps', true);
foreach ($deps as $name => $dep) {
    // revision
    if (isset($versions[$name])) {
        $rev = $versions[$name];
    } else {
        $rev = isset($dep['version']) ? $dep['version'] : 'origin/HEAD';
    }

    // install dir
    $installDir = isset($dep['target']) ? $vendorDir.'/'.$dep['target'] : $vendorDir.'/'.$name;
    if (in_array('--reinstall', $argv)) {
        if (PHP_OS == 'WINNT') {
            system(sprintf('rmdir /S /Q %s', escapeshellarg(realpath($installDir))));
        } else {
            system(sprintf('rm -rf %s', escapeshellarg($installDir)));
        }
    }

    echo "> Installing/Updating $name\n";

    // url
    if (!isset($dep['git'])) {
        exit(sprintf('The "git" value for the "%s" dependency must be set.', $name));
    }
    $url = $dep['git'];
    var_dump($url);
    
    if (!is_dir($installDir)) {
        system(sprintf('git clone %s %s', escapeshellarg($url), escapeshellarg($installDir)));
    }
    
    system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));

    if ('update' === $command) {
        ob_start();
        system(sprintf('cd %s && git log -n 1 --format=%%H', escapeshellarg($installDir)));
        $newversions[] = trim($name.' '.ob_get_clean());
    }
}

// update?
if ('update' === $command) {
    file_put_contents($rootDir.'/deps.lock', implode("\n", $newversions));
}