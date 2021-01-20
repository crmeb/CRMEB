<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi;

use OpenApi\Annotations\OpenApi;
use Symfony\Component\Finder\Finder;

if (defined('OpenApi\UNDEFINED') === false) {
    /*
     * Special value to differentiate between null and undefined.
     */
    define('OpenApi\UNDEFINED', '@OA\UNDEFINED🙈');
    define('OpenApi\Annotations\UNDEFINED', UNDEFINED);
    define('OpenApi\Processors\UNDEFINED', UNDEFINED);
}

// PHP 8.0
if (!defined('T_NAME_QUALIFIED')) {
    define('T_NAME_QUALIFIED', -4);
}
if (!defined('T_NAME_FULLY_QUALIFIED')) {
    define('T_NAME_FULLY_QUALIFIED', -5);
}

if (function_exists('OpenApi\scan') === false) {
    /**
     * Scan the filesystem for OpenAPI annotations and build openapi-documentation.
     *
     * @param array|Finder|string $directory The directory(s) or filename(s)
     * @param array               $options
     *                                       exclude: string|array $exclude The directory(s) or filename(s) to exclude (as absolute or relative paths)
     *                                       pattern: string       $pattern File pattern(s) to scan (default: *.php)
     *                                       analyser: defaults to StaticAnalyser
     *                                       analysis: defaults to a new Analysis
     *                                       processors: defaults to the registered processors in Analysis
     *
     * @return OpenApi
     */
    function scan($directory, $options = [])
    {
        $analyser = array_key_exists('analyser', $options) ? $options['analyser'] : new StaticAnalyser();
        $analysis = array_key_exists('analysis', $options) ? $options['analysis'] : new Analysis();
        $processors = array_key_exists('processors', $options) ? $options['processors'] : Analysis::processors();
        $exclude = array_key_exists('exclude', $options) ? $options['exclude'] : null;
        $pattern = array_key_exists('pattern', $options) ? $options['pattern'] : null;

        // Crawl directory and parse all files
        $finder = Util::finder($directory, $exclude, $pattern);
        foreach ($finder as $file) {
            $analysis->addAnalysis($analyser->fromFile($file->getPathname()));
        }
        // Post processing
        $analysis->process($processors);
        // Validation (Generate notices & warnings)
        $analysis->validate();

        return $analysis->openapi;
    }
}
