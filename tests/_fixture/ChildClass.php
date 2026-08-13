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

final class ChildClass extends ParentClass
{
    private ?object $privateChildProperty;

    public function __construct(?object $private, ?object $protected, ?object $public, ?object $child)
    {
        parent::__construct($private, $protected, $public);

        $this->privateChildProperty = $child;
    }
}
