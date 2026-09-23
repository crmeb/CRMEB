# A trait to dynamically add methods to a class

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/macroable.svg?style=flat-square)](https://packagist.org/packages/spatie/macroable)
![run-tests](https://github.com/spatie/macroable/workflows/run-tests/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/macroable.svg?style=flat-square)](https://packagist.org/packages/spatie/macroable)

This package provides a trait that, when applied to a class, makes it possible to add methods to that class at runtime.

Here's a quick example:

```php
$myClass = new class() {
    use Spatie\Macroable\Macroable;
};

$myClass::macro('concatenate', function(... $strings) {
   return implode('-', $strings);
});

$myClass->concatenate('one', 'two', 'three'); // returns 'one-two-three'
```

The idea of a macroable trait and the implementation is taken from [the `macroable` trait](https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Traits/Macroable.php) of the [Laravel framework](https://laravel.com).

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/macroable.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/macroable)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

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

$macroableClass->concatenate('one', 'two', 'three'); // returns 'one-two-three'
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

You can also add multiple methods in one go by using a mixin class. A mixin class contains methods that return callables. Each method from the mixin will be registered on the macroable class.

```php
$mixin = new class() {
    public function mixinMethod()
    {
       return function() {
          return 'mixinMethod';
       };
    }
    
    public function anotherMixinMethod()
    {
       return function() {
          return 'anotherMixinMethod';
       };
    }
};

$macroableClass->mixin($mixin);

$macroableClass->mixinMethod() // returns 'mixinMethod';

$macroableClass->anotherMixinMethod() // returns 'anotherMixinMethod';
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Kruikstraat 22, 2018 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

Idea and code is taken from [the `macroable` trait](https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Traits/Macroable.php) of the [Laravel framework](https://laravel.com).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
