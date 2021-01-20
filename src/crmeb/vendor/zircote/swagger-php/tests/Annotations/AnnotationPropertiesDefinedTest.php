<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Annotations;

use function get_class_vars;
use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Tests\OpenApiTestCase;

class AnnotationPropertiesDefinedTest extends OpenApiTestCase
{
    /**
     * @dataProvider allAnnotationClasses
     */
    public function testPropertiesAreNotUndefined($annotation)
    {
        $properties = get_class_vars($annotation);
        $skip = AbstractAnnotation::$_blacklist;
        foreach ($properties as $property => $value) {
            if (in_array($property, $skip)) {
                continue;
            }
            if ($value === null) {
                $this->fail('Property '.basename($annotation).'->'.$property.' should be DEFINED');
            }
        }
    }
}
