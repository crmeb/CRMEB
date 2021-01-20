<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi;

use Exception;

if (class_exists('Doctrine\Common\Annotations\AnnotationRegistry', true)) {
    // Using doctrine/annotation 1.x
    // Load all whitelisted annotations
    \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(
        function ($class) {
            if (Analyser::$whitelist === false) {
                $whitelist = ['OpenApi\Annotations\\'];
            } else {
                $whitelist = Analyser::$whitelist;
            }
            foreach ($whitelist as $namespace) {
                if (strtolower(substr($class, 0, strlen($namespace))) === strtolower($namespace)) {
                    $loaded = class_exists($class);
                    if (!$loaded && $namespace === 'OpenApi\Annotations\\') {
                        if (in_array(strtolower(substr($class, 20)), ['definition', 'path'])) { // Detected an 2.x annotation?
                            throw new Exception('The annotation @SWG\\'.substr($class, 20).'() is deprecated. Found in '.Analyser::$context."\nFor more information read the migration guide: https://github.com/zircote/swagger-php/blob/master/docs/Migrating-to-v3.md");
                        }
                    }

                    return $loaded;
                }
            }

            return false;
        }
    );
}
/**
 * Extract swagger-php annotations from a [PHPDoc](http://en.wikipedia.org/wiki/PHPDoc) using Doctrine's DocParser.
 */
class Analyser
{
    /**
     * List of namespaces that should be detected by the doctrine annotation parser.
     * Set to false to load all detected classes.
     *
     * @var array|false
     */
    public static $whitelist = ['OpenApi\Annotations\\'];

    /**
     * Use @OA\* for OpenAPI annotations (unless overwritten by a use statement).
     */
    public static $defaultImports = ['oa' => 'OpenApi\Annotations'];

    /**
     * Allows Annotation classes to know the context of the annotation that is being processed.
     *
     * @var Context
     */
    public static $context;

    /**
     * @var DocParser
     */
    public $docParser;

    public function __construct($docParser = null)
    {
        if ($docParser === null) {
            if (class_exists('Doctrine\Annotations\DocParser', true)) {
                // Using doctrine/annotation 2.x
                $docParser = new \Doctrine\Annotations\DocParser();
            } else {
                // Using doctrine/annotation 1.x
                $docParser = new \Doctrine\Common\Annotations\DocParser();
            }
            $docParser->setIgnoreNotImportedAnnotations(true);
            $docParser->setImports(static::$defaultImports);
        }
        $this->docParser = $docParser;
    }

    /**
     * Use doctrine to parse the comment block and return the detected annotations.
     *
     * @param string  $comment a T_DOC_COMMENT
     * @param Context $context
     *
     * @return array Annotations
     */
    public function fromComment($comment, $context = null)
    {
        if ($context === null) {
            $context = new Context(['comment' => $comment]);
        } else {
            $context->comment = $comment;
        }
        try {
            self::$context = $context;
            if ($context->is('annotations') === false) {
                $context->annotations = [];
            }
            $comment = preg_replace_callback(
                '/^[\t ]*\*[\t ]+/m',
                function ($match) {
                    // Replace leading tabs with spaces.
                    // Workaround for http://www.doctrine-project.org/jira/browse/DCOM-255
                    return str_replace("\t", ' ', $match[0]);
                },
                $comment
            );
            $annotations = $this->docParser->parse($comment, $context);
            self::$context = null;

            return $annotations;
        } catch (Exception $e) {
            self::$context = null;
            if (preg_match('/^(.+) at position ([0-9]+) in '.preg_quote((string) $context, '/').'\.$/', $e->getMessage(), $matches)) {
                $errorMessage = $matches[1];
                $errorPos = (int) $matches[2];
                $atPos = strpos($comment, '@');
                $context->line += substr_count($comment, "\n", 0, $atPos + $errorPos);
                $lines = explode("\n", substr($comment, $atPos, $errorPos));
                $context->character = strlen(array_pop($lines)) + 1; // position starts at 0 character starts at 1
                Logger::warning(new Exception($errorMessage.' in '.$context, $e->getCode(), $e));
            } else {
                Logger::warning($e);
            }

            return [];
        }
    }
}
