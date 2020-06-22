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

use PHPUnit\Framework\TestCase;
use SebastianBergmann\ObjectEnumerator\Fixtures\ExceptionThrower;
use stdClass;

/**
 * @covers \SebastianBergmann\ObjectEnumerator\Enumerator
 */
class EnumeratorTest extends TestCase
{
    /**
     * @var Enumerator
     */
    private $enumerator;

    protected function setUp(): void
    {
        $this->enumerator = new Enumerator;
    }

    public function testEnumeratesSingleObject(): void
    {
        $a = new stdClass;

        $objects = $this->enumerator->enumerate($a);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testEnumeratesArrayWithSingleObject(): void
    {
        $a = new stdClass;

        $objects = $this->enumerator->enumerate([$a]);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testEnumeratesArrayWithTwoReferencesToTheSameObject(): void
    {
        $a = new stdClass;

        $objects = $this->enumerator->enumerate([$a, $a]);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testEnumeratesArrayOfObjects(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $objects = $this->enumerator->enumerate([$a, $b, null]);

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

        $objects = $this->enumerator->enumerate($a);

        $this->assertCount(2, $objects);
        $this->assertSame($a, $objects[0]);
        $this->assertSame($b, $objects[1]);
    }

    public function testEnumeratesObjectWithAggregatedObjectsInArray(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $a->b = [$b];

        $objects = $this->enumerator->enumerate($a);

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

        $objects = $this->enumerator->enumerate([$a, $b]);

        $this->assertCount(2, $objects);
        $this->assertSame($a, $objects[0]);
        $this->assertSame($b, $objects[1]);
    }

    public function testEnumeratesClassThatThrowsException(): void
    {
        $thrower = new ExceptionThrower();

        $objects = $this->enumerator->enumerate($thrower);

        $this->assertSame($thrower, $objects[0]);
    }

    public function testExceptionIsRaisedForInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->enumerator->enumerate(null);
    }

    public function testExceptionIsRaisedForInvalidArgument2(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->enumerator->enumerate([], '');
    }
}
