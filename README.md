# emitter

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Travis](https://img.shields.io/travis/bfg/emitter.svg?style=flat-square)]()
[![Total Downloads](https://img.shields.io/packagist/dt/bfg/emitter.svg?style=flat-square)](https://packagist.org/packages/bfg/emitter)

## Install
`composer require bfg/emitter`

## Usage
Set in you `RouteServiceProvider` router connection:
```php
\Route::emitter();
```
For using with any guard use:
```php
\Route::emitter('sanctum');
```

## Blade
```html
@emitterScripts();
<!-- OR --->
@emitterScripts($options);
```
Possible options is:
- headers
- domain

For request configuring.

## JavaScript
```javascript
$message('my-event', {});
```

Message is a signed request for an event.
What to transmit the names of the events and
at the same time not to transmit its full
range of names, the system is looking for
nesting in any space that is compiled depending
on your security guard, the default is `web`
So your nesting prefix will be the next `WebMessage`
And all created and declared Events and will cause them
consistently if there will be several events in one name.

Event search occurs on the following pattern:
> Send name: `my-event` or `my`;
>
> Called Event: `*`\WebMessage\MyEvent

> Send name: `actions:my-event` or `actions:my`;
>
> Called Event: `*`\WebMessageActions\MyEvent

> `*` - Maybe any value.

### VueJs Mixin
```javascript
Vue.mixin(VueMessageMutator);
```
### AlpineJs
Messages are supported by AlpineJS by default.

## Event protection
You can create a method with the name `access` in the event class, which is executed before performing an event, but after its design.
```php
    ...
    public function access() {
        return \Auth::check() && \Auth::user()->isAdmin()
    } 
    ...
```

## Resource wrapping
You can declare a property or method named `resource`, he must return the resource class that needs to be applied to the last truthful listener's result.
```php
    ...
    public $resource = UserResource::class; 
    // OR
    public function resource() {
        return UserResource::class;
    } 
    ...
```

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Credits

- [Xsaven](https://github.com/bfg)
- [All Contributors](https://github.com/bfg/emitter/contributors)

## Security
If you discover any security-related issues, please email xsaven@gmail.com instead of using the issue tracker.

## License
The MIT License (MIT). Please see [License File](/LICENSE.md) for more information.
