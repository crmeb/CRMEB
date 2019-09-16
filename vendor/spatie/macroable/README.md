# A trait to dynamically add methods to a class

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/macroable.svg?style=flat-square)](https://packagist.org/packages/spatie/macroable)
[![Build Status](https://img.shields.io/travis/spatie/macroable/master.svg?style=flat-square)](https://travis-ci.org/spatie/macroable)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/c3dbed0e-9794-4c54-b40c-ccaf5a1394de.svg?style=flat-square)](https://insight.sensiolabs.com/projects/c3dbed0e-9794-4c54-b40c-ccaf5a1394de)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/macroable.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/macroable)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/macroable.svg?style=flat-square)](https://packagist.org/packages/spatie/macroable)

This package provides a trait that, when applied to a class, makes it possible to add methods to that class at runtime.

Here's a quick example:

```php
$myClass = new class() {
    use Spatie\Macroable\Macroable;
};

$myClass::macro('concatenate', function(... $strings) {
   return implode('-', $strings);
};

$myClass->concatenate('one', 'two', 'three'); // returns 'one-two-three'
```

The idea of a macroable trait and the implementation is taken from [the `macroable` trait](https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Traits/Macroable.php) of the [the Laravel framework](https://laravel.com).


## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/macroable
```

## Usage

You can add a new method to a class using `macro`:

```php
$macroableClass = new class() {
    use Spatie\Macroable\Macroable;
};

$macroableClass::macro('concatenate', function(... $strings) {
   return implode('-', $strings);
};

$myClass->concatenate('one', 'two', 'three'); // returns 'one-two-three'
```

Callables passed to the `macro` function will be bound to the `class`

```php
$macroableClass = new class() {
    
    protected $name = 'myName';
    
    use Spatie\Macroable\Macroable;
};

$macroableClass::macro('getName', function() {
   return $this->name;
};

$macroableClass->getName(); // returns 'myName'
```

You can also add multiple methods in one go my using a mixin class. A mixin class contains methods that return callables. Each method from the mixin will be registered on the macroable class.

```php
$mixin = new class() {
    public function mixinMethod()
    {
       return 'mixinMethod';
    }
    
    public function anotherMixinMethod()
    {
       return 'anotherMixinMethod';
    }
};

$macroableClass->mixin($mixin);

$macroableClass->mixinMethod() // returns 'mixinMethod';

$macroableClass->anotherMixinMethod() // returns 'anotherMixinMethod';
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

Idea and code is taken from [the `macroable` trait](https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Traits/Macroable.php) of the [the Laravel framework](https://laravel.com).

## About Spatie

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
