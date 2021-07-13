<?php

namespace Songshenzong\Support\Traits;

/**
 * Trait Uri
 *
 * @package Songshenzong\Support\Traits
 */
trait Uri
{

    /**
     * @param string $uri
     * @param array  $parameters
     * @param bool   $appendsCurrentUri
     *
     * @return string
     */
    public static function uri($uri, array $parameters = [], $appendsCurrentUri = false)
    {
        $uriComponents = parse_url($uri);

        if (isset($uriComponents['query'])) {
            \parse_str($uriComponents['query'], $uriComponents['query']);
        } else {
            $uriComponents['query'] = [];
        }

        if ($appendsCurrentUri) {
            $newQuery = $parameters + $_GET + $uriComponents['query'];
        } else {
            $newQuery = $parameters + $uriComponents['query'];
        }

        $newUriComponents = isset($uriComponents['scheme']) ? $uriComponents['scheme'] . '://' : '';
        $newUriComponents .= isset($uriComponents['host']) ? $uriComponents['host'] : '';
        $newUriComponents .= isset($uriComponents['port']) ? ':' . $uriComponents['port'] : '';
        $newUriComponents .= isset($uriComponents['path']) ? $uriComponents['path'] : '';
        $newUriComponents .= '?' . http_build_query($newQuery);

        return $newUriComponents;
    }

    /**
     * @param $host
     *
     * @return bool
     */
    public static function host($host)
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return false;
        }
        $lower = \strtolower($_SERVER['HTTP_HOST']);

        if (\is_string($host)) {
            return $host === $lower;
        }

        if (\is_array($host)) {
            foreach ($host as $item) {
                if ($item === $lower) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
}
