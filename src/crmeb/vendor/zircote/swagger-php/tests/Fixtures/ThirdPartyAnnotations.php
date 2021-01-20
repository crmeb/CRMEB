<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures;

/**
 * @OA\Info(title="Fixture for ParserTest", version="test")
 * Based on the examplefrom http://framework.zend.com/manual/current/en/modules/zend.form.quick-start.html
 */
use AnotherNamespace\Annotations as Annotation;

/**
 * @Annotation\Unrelated("user")
 */
class ThirdPartyAnnotations
{
    /**
     * @Annotation\Unrelated()
     */
    public $id;

    /**
     * @Annotation\Unrelated("user")
     */
    public $username;

    /**
     * @Annotation\Unrelated("email")
     */
    public $email;

    /**
     * @OA\Get(path="api/3rd-party", @OA\Response(response="200", description="a response"))
     */
    public function methodWithOpenApiAnnotation()
    {
    }
}
