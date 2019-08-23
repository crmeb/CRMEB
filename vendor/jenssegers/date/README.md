Date
====

[![Latest Stable Version](http://img.shields.io/github/release/jenssegers/date.svg)](https://packagist.org/packages/jenssegers/date) [![Total Downloads](http://img.shields.io/packagist/dm/jenssegers/date.svg)](https://packagist.org/packages/jenssegers/date) [![Build Status](http://img.shields.io/travis/jenssegers/date.svg)](https://travis-ci.org/jenssegers/date) [![Coverage Status](https://coveralls.io/repos/github/jenssegers/date/badge.svg?branch=master)](https://coveralls.io/github/jenssegers/date?branch=master) [![Donate](https://img.shields.io/badge/donate-paypal-blue.svg)](https://www.paypal.me/jenssegers)

This date library extends [Carbon](https://github.com/briannesbitt/Carbon) with multi-language support. Methods such as `format`, `diffForHumans`, `parse`, `createFromFormat` and the new `timespan`, will now be translated based on your locale.

Installation
------------

Install using composer:

```bash
composer require jenssegers/date
```

Laravel
-------

There is a service provider included for integration with the Laravel framework. This provider will get the application locale setting and use this for translations. To register the service provider, add the following to the providers array in `config/app.php`:

```php
'Jenssegers\Date\DateServiceProvider',
```

You can also add it as a Facade in `config/app.php`:

```php
'Date' => Jenssegers\Date\Date::class,
```

Languages
---------

This package contains language files for the following languages:

 - Albanian
 - Arabic
 - Azerbaijani
 - Bangla
 - Basque
 - Belarusian
 - Bosnian
 - Brazilian Portuguese
 - Bulgarian
 - Catalan
 - Croatian
 - Chinese Simplified
 - Chinese Traditional
 - Czech
 - Danish
 - Dutch
 - English
 - Esperanto
 - Estonian
 - Finnish
 - French
 - Galician
 - Georgian
 - German
 - Greek
 - Hebrew
 - Hindi
 - Hungarian
 - Icelandic
 - Indonesian
 - Italian
 - Japanese
 - Kazakh
 - Korean
 - Latvian
 - Lithuanian
 - Macedonian
 - Malay
 - Norwegian
 - Nepali (नेपाली)
 - Polish
 - Portuguese
 - Persian (Farsi)
 - Romanian
 - Russian
 - Thai
 - Serbian (latin)
 - Serbian (cyrillic)
 - Slovak
 - Slovenian
 - Spanish
 - Swedish
 - Turkish
 - Turkmen
 - Ukrainian
 - Uzbek
 - Vietnamese
 - Welsh

Usage
-----

The Date class extends the Carbon methods such as `format` and `diffForHumans`n and translates them based on your locale:

```php
use Jenssegers\Date\Date;

Date::setLocale('nl');

echo Date::now()->format('l j F Y H:i:s'); // zondag 28 april 2013 21:58:16

echo Date::parse('-1 day')->diffForHumans(); // 1 dag geleden
```

The Date class also added some aliases and additional methods such as: `ago` which is an alias for `diffForHumans`, and the `timespan` method:

```php
echo $date->timespan(); // 3 months, 1 week, 1 day, 3 hours, 20 minutes
```

Methods such as `parse` and `createFromFormat` also support "reverse translations". When calling these methods with translated input, it will try to translate it to English before passing it to DateTime:

```php
$date = Date::createFromFormat('l d F Y', 'zaterdag 21 maart 2015');
```

Carbon
------

Carbon is the library the Date class is based on. All of the original Carbon operations are still available, check out https://github.com/briannesbitt/Carbon for more information. Here are some of the available methods:

### Creating dates

You can create Date objects just like the DateTime object (http://www.php.net/manual/en/datetime.construct.php):

```php
$date = new Date();
$date = new Date('2000-01-31');
$date = new Date('2000-01-31 12:00:00');

// With time zone
$date = new Date('2000-01-31', new DateTimeZone('Europe/Brussels'));
```

You can skip the creation of a DateTimeZone object:

```php
$date = new Date('2000-01-31', 'Europe/Brussels');
```

Create Date objects from a relative format (http://www.php.net/manual/en/datetime.formats.relative.php):

```php
$date = new Date('now');
$date = new Date('today');
$date = new Date('+1 hour');
$date = new Date('next monday');
```

This is also available using these static methods:

```php
$date = Date::parse('now');
$date = Date::now();
```

Creating a Date from a timestamp:

```php
$date = new Date(1367186296);
```

Or from an existing date or time:

```php
$date = Date::createFromDate(2000, 1, 31);
$date = Date::createFromTime(12, 0, 0);
$date = Date::create(2000, 1, 31, 12, 0, 0);
```

### Formatting Dates

You can format a Date object like the DateTime object (http://www.php.net/manual/en/function.date.php):

```php
echo Date::now()->format('Y-m-d'); // 2000-01-31
```

The Date object can be cast to a string:

```php
echo Date::now(); // 2000-01-31 12:00:00
```

Get a human readable output (alias for diffForHumans):

```php
echo $date->ago(); // 5 days ago
```

Calculate a timespan:

```php
$date = new Date('+1000 days');
echo Date::now()->timespan($date);
// 2 years, 8 months, 3 weeks, 5 days

// or even
echo Date::now()->timespan('+1000 days');
```

Get years since date:

```php
$date = new Date('-10 years');
echo $date->age; // 10

$date = new Date('+10 years');
echo $date->age; // -10
```

### Manipulating Dates

You can manipulate by using the *add* and *sub* methods, with relative intervals (http://www.php.net/manual/en/datetime.formats.relative.php):

```php
$yesterday = Date::now()->sub('1 day');
$tomorrow  = Date::now()->add('1 day');

// ISO 8601
$date = Date::now()->add('P2Y4DT6H8M');
```

You can access and modify all date attributes as an object:

```php
$date->year = 2013:
$date->month = 1;
$date->day = 31;

$date->hour = 12;
$date->minute = 0;
$date->second = 0;
```

Contributing
------------

You can easily add new languages by adding a new language file to the *lang* directory. These language entries support pluralization. By using a "pipe" character, you may separate the singular and plural forms of a string:

```php
'hour'      => '1 hour|:count hours',
'minute'    => '1 minute|:count minutes',
'second'    => '1 second|:count seconds',
```

Some languages have a different unit translation when they are used in combination with a suffix like 'ago'. For those situations you can add additional translations by adding the suffix to the unit as a key:

```php
'year'          => '1 Jahr|:count Jahre',
'year_ago'      => '1 Jahr|:count Jahren',
```

There is also a `generator.php` script that can be used to quickly output day and month translations for a specific locale. If you want to add a new language, this can speed up the process:

```bash
php generator.php nl_NL
```

**NOTE!** If you are adding languages, please check the rules about the capitalization of month and day names: http://meta.wikimedia.org/wiki/Capitalization_of_Wiktionary_pages#Capitalization_of_month_names

## License

Laravel Date is licensed under [The MIT License (MIT)](LICENSE).
