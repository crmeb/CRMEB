<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Processors;

use OpenApi\Annotations\Delete;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Post;
use OpenApi\Processors\OperationId;
use OpenApi\Tests\OpenApiTestCase;

class OperationIdTest extends OpenApiTestCase
{
    public function testOperationId()
    {
        $analysis = $this->analysisFromFixtures([
            'Processors/EntityControllerClass.php',
            'Processors/EntityControllerInterface.php',
            'Processors/EntityControllerTrait.php',
        ]);
        $analysis->process([new OperationId()]);
        $operations = $analysis->getAnnotationsOfType(Operation::class);

        $this->assertCount(3, $operations);

        $this->assertSame('entity/{id}', $operations[0]->path);
        $this->assertInstanceOf(Get::class, $operations[0]);
        $this->assertSame('OpenApi\Tests\Fixtures\Processors\EntityControllerClass::getEntry', $operations[0]->operationId);

        $this->assertSame('entity/{id}', $operations[1]->path);
        $this->assertInstanceOf(Post::class, $operations[1]);
        $this->assertSame('OpenApi\Tests\Fixtures\Processors\EntityControllerInterface::updateEntity', $operations[1]->operationId);

        $this->assertSame('entities/{id}', $operations[2]->path);
        $this->assertInstanceOf(Delete::class, $operations[2]);
        $this->assertSame('OpenApi\Tests\Fixtures\Processors\EntityControllerTrait::deleteEntity', $operations[2]->operationId);
    }
}
