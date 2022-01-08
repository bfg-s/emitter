<?php

namespace Bfg\Emitter;

use Illuminate\Http\Request;

class MessageController
{
    protected $resource = null;

    /**
     * @throws \ReflectionException
     */
    public function __invoke($name, Request $request)
    {
        $eventPattern = "*\\" . \Str::of($name)
            ->prepend(config('auth.defaults.guard'), 'Message', \Str::contains($name, ":") ? '-' : ':')
            ->camel()
            ->explode(':')
            ->map(function ($i) { return ucfirst($i); })
            ->join('\\');

        $results = [];

        foreach (LaravelHooks::getEvents() as $eventClass) {
            if (\Str::is($eventPattern, $eventClass) || \Str::is($eventPattern . "Event", $eventClass)) {
                foreach (array_filter($this->callEvent($eventClass, $request)) as $result) {
                    if (is_array($result) && !isset($result[0])) {
                        $results = array_merge($results, $result);
                    }
                }
            }
        }

        if ($this->resource) {

            $results = $this->resource::make($results);
        }

        return $results;
    }

    protected function callEvent(string $class, Request $request)
    {
        $event = app($class, $request->all());

        if (method_exists($event, 'access') && !$event->access()) {
            return null;
        }

        if (method_exists($event, 'resource')) {
            $this->resource = call_user_func($event->resource);
        } else if (property_exists($event, 'resource')) {
            $this->resource = $event->resource;
        }

        return event(
            $event
        );
    }

    public function verify()
    {
        return \session()->token() ?? abort(400, 'Injection error!');
    }
}
