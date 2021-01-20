<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Components;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\PathItem;
use OpenApi\Annotations\Schema;
use OpenApi\Processors\AugmentProperties;
use OpenApi\Processors\AugmentSchemas;
use OpenApi\Processors\BuildPaths;
use OpenApi\Processors\CleanUnmerged;
use OpenApi\Processors\InheritInterfaces;
use OpenApi\Processors\InheritProperties;
use OpenApi\Processors\InheritTraits;
use OpenApi\Processors\MergeIntoComponents;
use OpenApi\Processors\MergeIntoOpenApi;
use OpenApi\Tests\OpenApiTestCase;
use const OpenApi\UNDEFINED;

class InheritPropertiesTest extends OpenApiTestCase
{
    protected function validate(Analysis $analysis)
    {
        $analysis->openapi->info = new Info(['title' => 'test', 'version' => '1.0.0']);
        $analysis->openapi->paths = [new PathItem(['path' => '/test'])];
        $analysis->validate();
    }

    public function testInheritProperties()
    {
        $analysis = $this->analysisFromFixtures(
            [
                'AnotherNamespace/Child.php',
                'InheritProperties/GrandAncestor.php',
                'InheritProperties/Ancestor.php',
            ]
        );
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new InheritInterfaces(),
            new InheritTraits(),
            new AugmentSchemas(),
            new AugmentProperties(),
            new BuildPaths(),
        ]);
        $this->validate($analysis);

        $schemas = $analysis->getAnnotationsOfType(Schema::class);
        $childSchema = $schemas[0];
        $this->assertSame('Child', $childSchema->schema);
        $this->assertCount(1, $childSchema->properties);

        $analysis->process([
            new InheritProperties(),
            new CleanUnmerged(),
        ]);
        $this->validate($analysis);

        $this->assertCount(3, $childSchema->properties);
    }

    /**
     * Tests, if InheritProperties works even without any
     * docBlocks at all in the parent class.
     */
    public function testInheritPropertiesWithoutDocBlocks()
    {
        $analysis = $this->analysisFromFixtures([
            // this class has docblocks
            'AnotherNamespace/ChildWithDocBlocks.php',
            // this one doesn't
            'InheritProperties/AncestorWithoutDocBlocks.php',
        ]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new InheritInterfaces(),
            new InheritTraits(),
            new AugmentSchemas(),
            new AugmentProperties(),
            new BuildPaths(),
            new InheritProperties(),
            new CleanUnmerged(),
        ]);
        $this->validate($analysis);

        $schemas = $analysis->getAnnotationsOfType(Schema::class);
        $childSchema = $schemas[0];
        $this->assertSame('ChildWithDocBlocks', $childSchema->schema);
        $this->assertCount(1, $childSchema->properties);

        // no error occurs
        $analysis->process(new InheritProperties());
        $this->assertCount(1, $childSchema->properties);
    }

    /**
     * Tests inherit properties with all of block.
     */
    public function testInheritPropertiesWithAllOf()
    {
        $analysis = $this->analysisFromFixtures([
            // this class has all of
            'InheritProperties/Extended.php',
            'InheritProperties/Base.php',
        ]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new InheritInterfaces(),
            new InheritTraits(),
            new AugmentSchemas(),
            new AugmentProperties(),
            new BuildPaths(),
            new InheritProperties(),
            new CleanUnmerged(),
        ]);
//        $this->validate($analysis);

        $schemas = $analysis->getAnnotationsOfType(Schema::class, true);
        $this->assertCount(3, $schemas);

        /* @var Schema $extendedSchema */
        $extendedSchema = $schemas[0];
        $this->assertSame('ExtendedModel', $extendedSchema->schema);
        $this->assertSame(UNDEFINED, $extendedSchema->properties);

        $this->assertArrayHasKey(1, $extendedSchema->allOf);
        $this->assertEquals($extendedSchema->allOf[1]->properties[0]->property, 'extendedProperty');

        /* @var $includeSchemaWithRef Schema */
        $includeSchemaWithRef = $schemas[1];
        $this->assertSame(UNDEFINED, $includeSchemaWithRef->properties);
    }

    /**
     * Tests for inherit properties without all of block.
     */
    public function testInheritPropertiesWithOutAllOf()
    {
        $analysis = $this->analysisFromFixtures([
            // this class has all of
            'InheritProperties/ExtendedWithoutAllOf.php',
            'InheritProperties/Base.php',
        ]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new InheritInterfaces(),
            new InheritTraits(),
            new AugmentSchemas(),
            new AugmentProperties(),
            new BuildPaths(),
            new InheritProperties(),
            new CleanUnmerged(),
        ]);
        $this->validate($analysis);

        $schemas = $analysis->getAnnotationsOfType(Schema::class, true);
        $this->assertCount(2, $schemas);

        /* @var Schema $extendedSchema */
        $extendedSchema = $schemas[0];
        $this->assertSame('ExtendedWithoutAllOf', $extendedSchema->schema);
        $this->assertSame(UNDEFINED, $extendedSchema->properties);

        $this->assertCount(2, $extendedSchema->allOf);

        $this->assertEquals($extendedSchema->allOf[0]->ref, Components::SCHEMA_REF.'Base');
        $this->assertEquals($extendedSchema->allOf[1]->properties[0]->property, 'extendedProperty');
    }

    /**
     * Tests for inherit properties in object with two schemas in the same context.
     */
    public function testInheritPropertiesWitTwoChildSchemas()
    {
        $analysis = $this->analysisFromFixtures([
            // this class has all of
            'InheritProperties/ExtendedWithTwoSchemas.php',
            'InheritProperties/Base.php',
        ]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new InheritInterfaces(),
            new InheritTraits(),
            new AugmentSchemas(),
            new AugmentProperties(),
            new BuildPaths(),
            new InheritProperties(),
            new CleanUnmerged(),
        ]);
        $this->validate($analysis);

        $schemas = $analysis->getAnnotationsOfType(Schema::class, true);
        $this->assertCount(3, $schemas);

        /* @var Schema $extendedSchema */
        $extendedSchema = $schemas[0];
        $this->assertSame('ExtendedWithTwoSchemas', $extendedSchema->schema);
        $this->assertSame(UNDEFINED, $extendedSchema->properties);

        $this->assertCount(2, $extendedSchema->allOf);
        $this->assertEquals($extendedSchema->allOf[0]->ref, Components::SCHEMA_REF.'Base');
        $this->assertEquals($extendedSchema->allOf[1]->properties[0]->property, 'nested');
        $this->assertEquals($extendedSchema->allOf[1]->properties[1]->property, 'extendedProperty');

        /* @var  $nestedSchema Schema */
        $nestedSchema = $schemas[1];
        $this->assertSame(UNDEFINED, $nestedSchema->allOf);
        $this->assertCount(1, $nestedSchema->properties);
        $this->assertEquals($nestedSchema->properties[0]->property, 'nestedProperty');
    }

    /**
     * Tests inherit properties with interface.
     */
    public function testPreserveExistingAllOf()
    {
        $analysis = $this->analysisFromFixtures([
            'InheritProperties/BaseInterface.php',
            'InheritProperties/ExtendsBaseThatImplements.php',
            'InheritProperties/BaseThatImplements.php',
            'InheritProperties/TraitUsedByExtendsBaseThatImplements.php',
        ]);
        $analysis->process([
            new MergeIntoOpenApi(),
            new MergeIntoComponents(),
            new InheritInterfaces(),
            new InheritTraits(),
            new AugmentSchemas(),
            new AugmentProperties(),
            new BuildPaths(),
            new InheritProperties(),
            new CleanUnmerged(),
        ]);
        $this->validate($analysis);

        $analysis->openapi->info = new Info(['title' => 'test', 'version' => '1.0.0']);
        $analysis->openapi->paths = [new PathItem(['path' => '/test'])];
        $analysis->validate();

        /* @var Schema[] $schemas */
        $schemas = $analysis->getAnnotationsOfType(Schema::class, true);
        $this->assertCount(4, $schemas);

        $baseInterface = $schemas[0];
        $this->assertSame('BaseInterface', $baseInterface->schema);
        $this->assertEquals($baseInterface->properties[0]->property, 'interfaceProperty');
        $this->assertEquals(UNDEFINED, $baseInterface->allOf);

        $extendsBaseThatImplements = $schemas[1];
        $this->assertSame('ExtendsBaseThatImplements', $extendsBaseThatImplements->schema);
        $this->assertEquals(UNDEFINED, $extendsBaseThatImplements->properties);
        $this->assertNotEquals(UNDEFINED, $extendsBaseThatImplements->allOf);
        // base, trait and own properties
        $this->assertCount(3, $extendsBaseThatImplements->allOf);

        $baseThatImplements = $schemas[2];
        $this->assertSame('BaseThatImplements', $baseThatImplements->schema);
        $this->assertEquals(UNDEFINED, $baseThatImplements->properties);
        $this->assertNotEquals(UNDEFINED, $baseThatImplements->allOf);
        $this->assertCount(2, $baseThatImplements->allOf);

        $traitUsedByExtendsBaseThatImplements = $schemas[3];
        $this->assertSame('TraitUsedByExtendsBaseThatImplements', $traitUsedByExtendsBaseThatImplements->schema);
        $this->assertEquals($traitUsedByExtendsBaseThatImplements->properties[0]->property, 'traitProperty');
        $this->assertEquals(UNDEFINED, $traitUsedByExtendsBaseThatImplements->allOf);
    }
}
