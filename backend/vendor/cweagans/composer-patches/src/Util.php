<?php

namespace cweagans\Composer;

class Util
{
    /**
     * Contains pre-defined patch depths for various composer packages.
     *
     * You can open a pull request to add to this list. These values are *only* used
     * if there is no patch depth specified on the patch itself (in composer.json
     * or your patches file). Otherwise, the plugin will default to a depth of 1
     * (-p1 as an argument to git or patch).
     */
    public static function getDefaultPackagePatchDepth($package): ?int
    {
        // You can open a pull request to add to this list.
        // Please keep it alphabetized by vendor, then package name.
        $packages = [
            'drupal/core' => 2,
        ];

        return $packages[$package] ?? null;
    }
}
