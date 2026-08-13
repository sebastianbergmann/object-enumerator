<?php declare(strict_types=1);
/*
 * This file is part of sebastian/object-enumerator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\ObjectEnumerator\Fixtures;

use stdClass;

final class ClassWithVirtualProperty
{
    /**
     * A virtual property has no backing store; it fabricates a new object on
     * every read.
     */
    public object $virtual {
        get => new stdClass;
    }
}
