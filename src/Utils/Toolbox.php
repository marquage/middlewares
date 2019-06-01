<?php

namespace Marquage\Middlewares\Utils;

use CallbackFilterIterator;
use FilesystemIterator;
use RecursiveArrayIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait Toolbox
{

    /**
     * @param string $path
     * @param string $ext
     * @return array|false
     */
    public function mdGlob(string $path, string $ext = "md")
    {
        $pattern = $path . "/**/*." . $ext;
        $dirs = glob($path . '/*', GLOB_ONLYDIR);
        $files = glob($pattern);
        foreach ($dirs as $dir) {
            $subDirList = $this->mdGlob($dir);
            $files = array_merge($files, $subDirList);
        }
        return $files;
    }

    /**
     * @param string $path
     * @return array|false
     */
    public function dirGlob(string $path)
    {
        $results = [];
        while($dirs = glob($path . '/*', GLOB_ONLYDIR)) {
            $path .= '/*';
            if(!$results) {
                $results = $dirs;
            } else {
                $results = array_merge($results, $dirs);
            }
        }
        return $results;
    }

    /**
     * @param string $path
     * @param string $pattern
     * @param int    $flags
     * @return array|false
     */
    public function rglob($path='', $pattern='*', $flags = 0) {
        $paths=glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
        $files=glob($path.$pattern, $flags);
        foreach ($paths as $path) {
            $files=array_merge($files,$this->rglob($path, $pattern, $flags));
        }
        return $files;
    }

    /**
     * @param array $notFlat
     * @param array $flat
     * @return array
     */
    public static function flatten(array $notFlat, array $flat = []): array
    {
        $items = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($notFlat),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($items as $item) {
            $flat[] = $item;
        }
        return $flat;
    }

    /**
     * @param $slugged
     * @return string
     */
    public static function unSlug($slugged): string
    {
        if (is_array($slugged)) {
            return array_map('unSlug', $slugged);
        }
        return str_replace(['-', '_'], ' ', $slugged);
    }

    /**
     * @param string $string
     * @param string $separator
     * @return string
     */
    public function quickSlug(string $string, string $separator = '_'):string
    {
        return str_replace(' ', $separator, $string);
    }

    /**
     * @param string|null $url
     * @return string
     */
    public function siteUrl(string $url = null):string
    {
        $base = http_response_code() !== FALSE ? 'https://' . $_SERVER['HTTP_HOST'] . '/' : 'https://localhost';
        return $url ? $base.$url : $base;
    }

    public static function relativePath(string $string):string
    {
        if (substr($string, -3) === ".md") {
            $string = substr($string, 0, -3);
        }
        return str_replace(FILES . "/", '', $string);
    }
    /**
     * To ensure a given path is absolute, pointing to the "Notes" folder
     * @param string $dir
     * @return string
     */
    public static function checkDir(string $dir): string
    {
        return is_dir($dir) ? $dir : FILES . DIRECTORY_SEPARATOR . $dir;
    }
    public static function filesOnly($path = FILES, bool $shorten = null, array $out = []): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        $iterator = new CallbackFilterIterator($iterator , function ($file) {
            return ((strpos($file, ".trash") === false) && (substr($file, -3) === ".md"));
        });
        foreach ($iterator as $filename) {
            $out[self::relativePath("$filename")] = $shorten ? self::relativePath("$filename") : $filename;
        }
        return $out;
    }
    public static function getFolderTree(string $path, string $folders = ''): string
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if (is_dir($file)) $folders .= sprintf("<option value='%s'>%s</option>", self::relativePath($file->getPathname()), self::relativePath($file->getPathname()));
        }
        return $folders;
    }
}
