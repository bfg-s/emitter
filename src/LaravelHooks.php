<?php

namespace Bfg\Emitter;

class LaravelHooks
{
    /**
     * @throws \ReflectionException
     */
    public static function getEvents()
    {
        $events = app('events');
        $property = new \ReflectionProperty($events, "listeners");
        $property->setAccessible(true);
        return array_keys(
            $property->getValue($events)
        );
    }
}
