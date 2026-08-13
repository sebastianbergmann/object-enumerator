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

use function array_keys;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SebastianBergmann\ObjectEnumerator\Fixtures\ChildClass;
use SebastianBergmann\ObjectEnumerator\Fixtures\ClassWithHookedProperty;
use SebastianBergmann\ObjectEnumerator\Fixtures\ClassWithPublicProperties;
use SebastianBergmann\ObjectEnumerator\Fixtures\ClassWithThrowingPropertyHook;
use SebastianBergmann\ObjectEnumerator\Fixtures\ClassWithVirtualProperty;
use SebastianBergmann\ObjectEnumerator\Fixtures\ExceptionThrower;
use SebastianBergmann\ObjectEnumerator\Fixtures\ParentClass;
use SebastianBergmann\RecursionContext\Context;
use stdClass;

#[CoversClass(Enumerator::class)]
final class EnumeratorTest extends TestCase
{
    public function testEnumeratesEmptyArray(): void
    {
        $this->assertSame([], (new Enumerator)->enumerate([]));
    }

    public function testEnumeratesArrayWithNoObjects(): void
    {
        $this->assertSame([], (new Enumerator)->enumerate([1, 'string', 2.0, true, null]));
    }

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

    public function testEnumeratesObjectWithAggregatedObjectsInNestedArrays(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $a->b = [[['c' => $b]]];

        $objects = (new Enumerator)->enumerate($a);

        $this->assertCount(2, $objects);
        $this->assertSame($a, $objects[0]);
        $this->assertSame($b, $objects[1]);
    }

    public function testEnumeratesArrayWithTwoReferencesToTheSameArray(): void
    {
        $a = new stdClass;
        $b = [$a];

        $objects = (new Enumerator)->enumerate([$b, $b]);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testEnumeratesPrivateProtectedAndInheritedProperties(): void
    {
        $a = new stdClass;
        $b = new stdClass;
        $c = new stdClass;
        $d = new stdClass;

        $child = new ChildClass($a, $b, $c, $d);

        $objects = (new Enumerator)->enumerate($child);

        $this->assertCount(5, $objects);
        $this->assertSame($child, $objects[0]);
        $this->assertContains($a, $objects);
        $this->assertContains($b, $objects);
        $this->assertContains($c, $objects);
        $this->assertContains($d, $objects);
    }

    public function testEnumeratesPropertiesDeclaredInParentClass(): void
    {
        $a = new stdClass;

        $parent = new ParentClass($a, null, null);

        $objects = (new Enumerator)->enumerate($parent);

        $this->assertCount(2, $objects);
        $this->assertSame($parent, $objects[0]);
        $this->assertSame($a, $objects[1]);
    }

    public function testEnumeratesArrayThatReferencesItself(): void
    {
        $a = new stdClass;

        $array         = ['object' => $a];
        $array['self'] = &$array;

        $objects = (new Enumerator)->enumerate($array);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testEnumeratesArraysThatReferenceEachOther(): void
    {
        $a = new stdClass;

        $first  = [];
        $second = [];

        $first['second'] = &$second;
        $second['first'] = &$first;
        $first['object'] = $a;

        $objects = (new Enumerator)->enumerate($first);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testEnumeratesObjectThatAggregatesArrayThatReferencesItself(): void
    {
        $a = new stdClass;

        $array         = ['object' => $a];
        $array['self'] = &$array;

        $b       = new stdClass;
        $b->data = $array;

        $objects = (new Enumerator)->enumerate($b);

        $this->assertCount(2, $objects);
        $this->assertSame($b, $objects[0]);
        $this->assertSame($a, $objects[1]);
    }

    public function testDoesNotModifyArrayThatIsEnumerated(): void
    {
        $array = ['object' => new stdClass, 'value' => 1];

        (new Enumerator)->enumerate($array);

        $this->assertSame(['object', 'value'], array_keys($array));
    }

    public function testDoesNotEnumerateObjectsAlreadyContainedInContextThatIsPassed(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $context = new Context;

        $this->assertCount(1, (new Enumerator)->enumerate($a, $context));

        $objects = (new Enumerator)->enumerate([$a, $b], $context);

        $this->assertCount(1, $objects);
        $this->assertSame($b, $objects[0]);
    }

    public function testEnumeratesObjectAggregatedUsingBackedHookedProperty(): void
    {
        $a = new stdClass;
        $b = new ClassWithHookedProperty($a);

        $objects = (new Enumerator)->enumerate($b);

        $this->assertCount(2, $objects);
        $this->assertSame($b, $objects[0]);
        $this->assertSame($a, $objects[1]);
    }

    public function testEnumeratesObjectWithBackedHookedPropertyThatIsNull(): void
    {
        $a = new ClassWithHookedProperty(null);

        $objects = (new Enumerator)->enumerate($a);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testDoesNotEnumerateObjectsReturnedByVirtualProperty(): void
    {
        $a = new ClassWithVirtualProperty;

        $objects = (new Enumerator)->enumerate($a);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testDoesNotInvokeGetHookOfHookedProperty(): void
    {
        $a = new ClassWithThrowingPropertyHook;

        $objects = (new Enumerator)->enumerate($a);

        $this->assertCount(1, $objects);
        $this->assertSame($a, $objects[0]);
    }

    public function testDoesNotInitializeLazyGhost(): void
    {
        $initialized = false;

        $lazy = (new ReflectionClass(ClassWithPublicProperties::class))->newLazyGhost(
            static function (ClassWithPublicProperties $object) use (&$initialized): void
            {
                $initialized = true;

                $object->object = new stdClass;
            },
        );

        $objects = (new Enumerator)->enumerate($lazy);

        $this->assertFalse($initialized);
        $this->assertCount(1, $objects);
        $this->assertSame($lazy, $objects[0]);
    }

    public function testDoesNotInitializeLazyProxy(): void
    {
        $initialized = false;

        $lazy = (new ReflectionClass(ClassWithPublicProperties::class))->newLazyProxy(
            static function (ClassWithPublicProperties $object) use (&$initialized): ClassWithPublicProperties
            {
                $initialized = true;

                $real         = new ClassWithPublicProperties;
                $real->object = new stdClass;

                return $real;
            },
        );

        $objects = (new Enumerator)->enumerate($lazy);

        $this->assertFalse($initialized);
        $this->assertCount(1, $objects);
        $this->assertSame($lazy, $objects[0]);
    }

    public function testDoesNotInitializeLazyObjectThatIsAggregatedByAnotherObject(): void
    {
        $initialized = false;

        $lazy = (new ReflectionClass(ClassWithPublicProperties::class))->newLazyGhost(
            static function (ClassWithPublicProperties $object) use (&$initialized): void
            {
                $initialized = true;

                $object->object = new stdClass;
            },
        );

        $a          = new stdClass;
        $a->wrapped = [$lazy];

        $objects = (new Enumerator)->enumerate($a);

        $this->assertFalse($initialized);
        $this->assertCount(2, $objects);
        $this->assertSame($a, $objects[0]);
        $this->assertSame($lazy, $objects[1]);
    }

    public function testEnumeratesObjectsAggregatedByInitializedLazyObject(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $lazy = (new ReflectionClass(ClassWithPublicProperties::class))->newLazyGhost(
            static function (ClassWithPublicProperties $object) use ($a, $b): void
            {
                $object->object = $a;
                $object->array  = [$b];
            },
        );

        (new ReflectionClass(ClassWithPublicProperties::class))->initializeLazyObject($lazy);

        $objects = (new Enumerator)->enumerate($lazy);

        $this->assertCount(3, $objects);
        $this->assertSame($lazy, $objects[0]);
        $this->assertSame($a, $objects[1]);
        $this->assertSame($b, $objects[2]);
    }

    public function testEnumeratesClassThatThrowsException(): void
    {
        $thrower = new ExceptionThrower;

        $objects = (new Enumerator)->enumerate($thrower);

        $this->assertCount(1, $objects);
        $this->assertSame($thrower, $objects[0]);
    }
}
