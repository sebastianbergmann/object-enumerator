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

class ParentClass
{
    public ?object $publicProperty;
    protected ?object $protectedProperty;
    private ?object $privateProperty;

    public function __construct(?object $private, ?object $protected, ?object $public)
    {
        $this->privateProperty   = $private;
        $this->protectedProperty = $protected;
        $this->publicProperty    = $public;
    }
}
