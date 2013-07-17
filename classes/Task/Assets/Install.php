<?php defined('SYSPATH') or die('No direct script access.');

class Task_Assets_Install extends Minion_Task
{
    protected function _execute(array $params)
    {
        $assets = array();
        $files  = array();

        $assets[] = glob_recursive(MODPATH.'*/assets/*/img');
        $assets[] = glob_recursive(MODPATH.'*/assets/*/imgs');
        $assets[] = glob_recursive(MODPATH.'*/assets/*/image');
        $assets[] = glob_recursive(MODPATH.'*/assets/*/images');
        $assets[] = glob_recursive(MODPATH.'*/assets/*/font');
        $assets[] = glob_recursive(MODPATH.'*/assets/*/fonts');

        $assets = Arr::flatten($assets);

        foreach ($assets as $path) {
            foreach (glob($path.'/*') as $filename) {
                $files[basename($path)][] = $filename;
            }
        }

        $assetsdir = Kohana::$config->load('assets.installed_assets');

        foreach ($files as $directory => $paths) {
            $destination = $assetsdir.'/'.$directory;

            if (!is_dir($destination)) {
                if (is_writable($assetsdir)) {
                    mkdir($destination, 0777);
                } else {
                    Minion_CLI::write(Minion_CLI::color('Cant create assets folder. Directory '.$destination.' is not writeable.', 'red'));
                    return false;
                }
            } else {
                Minion_CLI::write(Minion_CLI::color('Removing old assets from '.$destination, 'yellow'));
                foreach (glob($destination.'/*') as $symlink) {
                    unlink($symlink);
                }
            }

            foreach ($paths as $path) {
                symlink($path, $destination.'/'.basename($path));
                Minion_CLI::write(Minion_CLI::color('Created symlink for '.$path, 'green'));
            }
        }
    }
}

function glob_recursive($pattern, $flags = 0) {
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
    }

    return $files;
}