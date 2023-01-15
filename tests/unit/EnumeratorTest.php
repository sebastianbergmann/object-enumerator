<?php declare(strict_types=1);
/*
 * This file is part of sebastian/object-enumerator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\ObjectEnumerator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\ObjectEnumerator\Fixtures\ExceptionThrower;
use stdClass;

#[CoversClass(Enumerator::class)]
class EnumeratorTest extends TestCase
{
    public function testEnumeratesSingleObject(): void
    {
        $a = new stdClass;

        $objects = (new Enumerator)->enumerate($a);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testEnumeratesArrayWithSingleObject(): void
    {
        $a = new stdClass;

        $objects = (new Enumerator)->enumerate([$a]);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testEnumeratesArrayWithTwoReferencesToTheSameObject(): void
    {
        $a = new stdClass;

        $objects = (new Enumerator)->enumerate([$a, $a]);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testEnumeratesArrayOfObjects(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $objects = (new Enumerator)->enumerate([$a, $b, null]);

        $this->assertCount(2, $objects);
        $this->assertSame($a, $objects[0]);
        $this->assertSame($b, $objects[1]);
    }

    public function testEnumeratesObjectWithAggregatedObject(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $a->b = $b;
        $a->c = null;

        $objects = (new Enumerator)->enumerate($a);

        $this->assertCount(2, $objects);
        $this->assertSame($a, $objects[0]);
        $this->assertSame($b, $objects[1]);
    }

    public function testEnumeratesObjectWithAggregatedObjectsInArray(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $a->b = [$b];

        $objects = (new Enumerator)->enumerate($a);

        $this->assertCount(2, $objects);
        $this->assertSame($a, $objects[0]);
        $this->assertSame($b, $objects[1]);
    }

    public function testEnumeratesObjectsWithCyclicReferences(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $a->b = $b;
        $b->a = $a;

        $objects = (new Enumerator)->enumerate([$a, $b]);

        $this->assertCount(2, $objects);
        $this->assertSame($a, $objects[0]);
        $this->assertSame($b, $objects[1]);
    }

    public function testEnumeratesClassThatThrowsException(): void
    {
        $thrower = new ExceptionThrower;

        $objects = (new Enumerator)->enumerate($thrower);

        $this->assertSame($thrower, $objects[0]);
    }
}
