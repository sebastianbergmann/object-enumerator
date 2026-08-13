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

use function is_array;
use function is_object;
use SebastianBergmann\RecursionContext\Context;

final class Enumerator
{
    /**
     * @param array<mixed>|object $variable
     *
     * @return list<object>
     */
    public function enumerate(array|object $variable, Context $processed = new Context): array
    {
        $objects = [];

        $this->process($variable, $processed, $objects);

        return $objects;
    }

    /**
     * @param array<mixed>|object $variable
     * @param list<object>        $objects
     */
    private function process(array|object &$variable, Context $processed, array &$objects): void
    {
        if ($processed->contains($variable) !== false) {
            return;
        }

        if (is_array($variable)) {
            /* The copy is made before the marker that Context::add() appends
             * to $variable, so that the marker is not traversed below. */
            $array = $variable;

            /* @noinspection UnusedFunctionResultInspection */
            $processed->add($variable);

            foreach ($array as &$element) {
                if (is_array($element) || is_object($element)) {
                    $this->process($element, $processed, $objects);
                }
            }

            return;
        }

        /* @noinspection UnusedFunctionResultInspection */
        $processed->add($variable);

        $objects[] = $variable;

        foreach ((array) $variable as $value) {
            if (is_array($value) || is_object($value)) {
                $this->process($value, $processed, $objects);
            }
        }
    }
}
