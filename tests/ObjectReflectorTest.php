<?php
/*
 * This file is part of Object Enumerator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\ObjectEnumerator;

use SebastianBergmann\ObjectEnumerator\Fixtures\ExceptionThrower;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\ObjectGraph\TestFixture\ChildClass;

/**
 * @covers SebastianBergmann\ObjectEnumerator\ObjectReflector
 */
class ObjectReflectorTest extends TestCase
{
    /**
     * @var ObjectReflector
     */
    private $objectReflector;

    protected function setUp()
    {
        $this->objectReflector = new ObjectReflector;
    }

    public function testReflectsAttributesOfObject()
    {
        $o = new ChildClass;

        $this->assertEquals(
            [
                'foo' => 'baz',
                'SebastianBergmann\ObjectGraph\TestFixture\ParentClass::foo' => 'bar'
            ],
            $this->objectReflector->getAttributes($o)
        );
    }
}
