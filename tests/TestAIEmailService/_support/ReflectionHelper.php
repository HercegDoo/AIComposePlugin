<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\TestAIEmailService;

trait ReflectionHelper
{
    /**
     * Find a private method invoker.
     *
     * @param object|string $obj    object or class name
     * @param string        $method method name
     *
     * @throws \ReflectionException
     *
     * @return \Closure
     */
    public static function getPrivateMethodInvoker($obj, $method)
    {
        $refMethod = new \ReflectionMethod($obj, $method);
        $refMethod->setAccessible(true);
        $obj = (\gettype($obj) === 'object') ? $obj : null;

        return static fn (...$args) => $refMethod->invokeArgs($obj, $args);
    }

    /**
     * Set a private property.
     *
     * @param object|string $obj      object or class name
     * @param string        $property property name
     * @param mixed         $value    value
     *
     * @throws \ReflectionException
     */
    public static function setPrivateProperty($obj, $property, $value)
    {
        $refProperty = self::getAccessibleRefProperty($obj, $property);

        if (\is_object($obj)) {
            $refProperty->setValue($obj, $value);
        } else {
            $refProperty->setValue(null, $value);
        }
    }

    /**
     * Retrieve a private property.
     *
     * @param object|string $obj      object or class name
     * @param string        $property property name
     *
     * @throws \ReflectionException
     *
     * @return mixed value
     */
    public static function getPrivateProperty($obj, $property)
    {
        $refProperty = self::getAccessibleRefProperty($obj, $property);

        return \is_string($obj) ? $refProperty->getValue() : $refProperty->getValue($obj);
    }

    /**
     * Find an accessible property.
     *
     * @param object|string $obj
     * @param string        $property
     *
     * @throws \ReflectionException
     *
     * @return \ReflectionProperty
     */
    private static function getAccessibleRefProperty($obj, $property)
    {
        $refClass = \is_object($obj) ? new \ReflectionObject($obj) : new \ReflectionClass($obj);

        $refProperty = $refClass->getProperty($property);
        $refProperty->setAccessible(true);

        return $refProperty;
    }
}
