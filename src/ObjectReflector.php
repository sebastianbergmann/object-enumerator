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

class ObjectReflector
{
    /**
     * @param object $object
     *
     * @return array
     */
    public function getAttributes($object)
    {
        $attributes = [];
        $reflector  = new \ReflectionObject($object);

        foreach ($reflector->getProperties() as $attribute) {
            $attribute->setAccessible(true);

            try {
                $attributes[$attribute->getName()] = $attribute->getValue($object);
            } catch (\Throwable $t) {
                continue;
            }
        }

        while ($reflector = $reflector->getParentClass()) {
            foreach ($reflector->getProperties() as $attribute) {
                $attribute->setAccessible(true);

                try {
                    $attributes[$attribute->getDeclaringClass()->getName() . '::' . $attribute->getName()] = $attribute->getValue($object);
                } catch (\Throwable $t) {
                    continue;
                }
            }
        }

        return $attributes;
    }
}
