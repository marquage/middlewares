<?php

namespace Marquage\Middlewares\Services;

use Cocur\Slugify\Slugify;

class SlugServices
{

    private static $caseToggle = ['lowercase' => false];

    /**
     * @param string $slug
     * @return string
     */
    public static function makeSlug(string $slug): string
    {
        $cocur = new Slugify(self::$caseToggle);
        $cocur->activateRuleSet('french');
        return $cocur->slugify($slug);
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
     * @param string $path
     * @return string
     */
    public static function normalize(string $path): string
    {
        $instance = new Slugify(self::$caseToggle);
        $instance->activateRuleSet('french');
        $array = (explode('/', $path));
        $out = [];
        foreach ($array as $k => $v) {
            $out[] = $instance->slugify($v);
        }
        return implode('/', $out);
    }
}
