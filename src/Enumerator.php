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

use SebastianBergmann\RecursionContext\Context;

class Enumerator
{
    /**
     * @param array|object $variable
     * @param Context      $processed
     *
     * @return object[]
     */
    public function enumerate($variable, Context $processed = null)
    {
        if (!is_array($variable) && !is_object($variable)) {
            throw new InvalidArgumentException;
        }

        if ($processed === null) {
            $processed = new Context;
        }

        $objects = [];

        if ($processed->contains($variable)) {
            return $objects;
        }

        if (is_array($variable)) {
            foreach ($variable as $element) {
                $objects = array_merge($objects, $this->enumerate($element, $processed));
            }
        } else {
            $objects[] = $variable;

            $reflector = new \ReflectionObject($variable);

            foreach ($reflector->getProperties() as $attribute) {
                $attribute->setAccessible(true);

                $value = $attribute->getValue($variable);

                if (!is_array($value) && !is_object($value)) {
                    continue;
                }

                $objects = array_merge($objects, $this->enumerate($value, $processed));
            }
        }

        $processed->add($variable);

        return $objects;
    }
}