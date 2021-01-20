<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests;

use OpenApi\Analysis;

class AnalysisTest extends OpenApiTestCase
{
    public function testRegisterProcessor()
    {
        $counter = 0;
        $analysis = new Analysis();
        $analysis->process();
        $this->assertSame(0, $counter);
        $countProcessor = function (Analysis $a) use (&$counter) {
            $counter++;
        };
        Analysis::registerProcessor($countProcessor);
        $analysis->process();
        $this->assertSame(1, $counter);
        Analysis::unregisterProcessor($countProcessor);
        $analysis->process();
        $this->assertSame(1, $counter);
    }

    public function testGetSubclasses()
    {
        $analysis = $this->analysisFromFixtures([
            'AnotherNamespace/Child.php',
            'InheritProperties/GrandAncestor.php',
            'InheritProperties/Ancestor.php',
        ]);

        $this->assertCount(3, $analysis->classes, '3 classes should\'ve been detected');

        $subclasses = $analysis->getSubClasses('\OpenApi\Tests\Fixtures\GrandAncestor');
        $this->assertCount(2, $subclasses, 'GrandAncestor has 2 subclasses');
        $this->assertSame(['\OpenApi\Tests\Fixtures\Ancestor', '\AnotherNamespace\Child'], array_keys($subclasses));
        $this->assertSame(['\AnotherNamespace\Child'], array_keys($analysis->getSubClasses('\OpenApi\Tests\Fixtures\Ancestor')));
    }

    public function testGetAncestorClasses()
    {
        $analysis = $this->analysisFromFixtures([
            'AnotherNamespace/Child.php',
            'InheritProperties/GrandAncestor.php',
            'InheritProperties/Ancestor.php',
        ]);

        $this->assertCount(3, $analysis->classes, '3 classes should\'ve been detected');

        $superclasses = $analysis->getSuperClasses('\AnotherNamespace\Child');
        $this->assertCount(2, $superclasses, 'Child has a chain of 2 super classes');
        $this->assertSame(['\OpenApi\Tests\Fixtures\Ancestor', '\OpenApi\Tests\Fixtures\GrandAncestor'], array_keys($superclasses));
        $this->assertSame(['\OpenApi\Tests\Fixtures\GrandAncestor'], array_keys($analysis->getSuperClasses('\OpenApi\Tests\Fixtures\Ancestor')));
    }

    public function testGetInterfacesOfClass()
    {
        $analysis = $this->analysisFromFixtures([
            'Parser/User.php',
            'Parser/UserInterface.php',
            'Parser/OtherInterface.php',
        ]);

        $this->assertCount(1, $analysis->classes);
        $this->assertCount(2, $analysis->interfaces);

        $interfaces = $analysis->getInterfacesOfClass('\OpenApi\Tests\Fixtures\Parser\User');
        $this->assertCount(2, $interfaces);
        $this->assertSame([
            '\OpenApi\Tests\Fixtures\Parser\UserInterface',
            '\OpenApi\Tests\Fixtures\Parser\OtherInterface',
        ], array_keys($interfaces));
    }

    public function testGetTraitsOfClass()
    {
        $analysis = $this->analysisFromFixtures([
            'Parser/User.php',
            'Parser/HelloTrait.php',
            'Parser/OtherTrait.php',
            'Parser/AsTrait.php',
            'Parser/StaleTrait.php',
        ]);

        $this->assertCount(1, $analysis->classes);
        $this->assertCount(4, $analysis->traits);

        $traits = $analysis->getTraitsOfClass('\OpenApi\Tests\Fixtures\Parser\User');
        $this->assertSame([
            '\OpenApi\Tests\Fixtures\Parser\HelloTrait',
            '\OpenApi\Tests\Fixtures\Parser\OtherTrait',
            '\OpenApi\Tests\Fixtures\Parser\AsTrait',
        ], array_keys($traits));
    }
}
