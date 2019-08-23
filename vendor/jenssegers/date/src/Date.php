<?php

namespace Jenssegers\Date;

use Carbon\Carbon;
use DateInterval;
use DateTimeZone;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

class Date extends Carbon
{
    /**
     * The Translator implementation.
     *
     * @var Translator
     */
    protected static $translator;

    /**
     * The fallback locale when a locale is not available.
     *
     * @var string
     */
    protected static $fallbackLocale = 'en';

    /**
     * The errors that can occur.
     *
     * @var array
     */
    protected static $lastErrors;

    /**
     * Returns new DateTime object.
     *
     * @param  string $time
     * @param  string|DateTimeZone $timezone
     */
    public function __construct($time = null, $timezone = null)
    {
        if (is_int($time)) {
            $timestamp = $time;
            $time = null;
        } else {
            $timestamp = null;
        }

        parent::__construct($time, $timezone);

        if ($timestamp !== null) {
            $this->setTimestamp($timestamp);
        }
    }

    /**
     * Create and return new Date instance.
     *
     * @param  string $time
     * @param  string|DateTimeZone $timezone
     * @return Date
     */
    public static function make($time = null, $timezone = null)
    {
        return static::parse($time, $timezone);
    }

    /**
     * Create a Date instance from a string.
     *
     * @param  string $time
     * @param  string|DateTimeZone $timezone
     * @return Date
     */
    public static function parse($time = null, $timezone = null)
    {
        if ($time instanceof Carbon) {
            return new static(
                $time->toDateTimeString(),
                $timezone ?: $time->getTimezone()
            );
        }

        if (!is_int($time)) {
            $time = static::translateTimeString($time);
        }

        return new static($time, $timezone);
    }

    /**
     * @inheritdoc
     */
    public static function createFromFormat($format, $time, $timezone = null)
    {
        $time = static::translateTimeString($time);

        return parent::createFromFormat($format, $time, $timezone);
    }

    /**
     * Alias for diffForHumans.
     *
     * @param  Date $since
     * @param  bool $absolute Removes time difference modifiers ago, after, etc
     * @return string
     */
    public function ago($since = null, $absolute = false)
    {
        return $this->diffForHumans($since, $absolute);
    }

    /**
     * Alias for diffForHumans.
     *
     * @param  Date $since
     * @return string
     */
    public function until($since = null)
    {
        return $this->ago($since);
    }

    /**
     * @inheritdoc
     */
    public function format($format)
    {
        $replace = [];

        // Loop all format characters and check if we can translate them.
        for ($i = 0; $i < strlen($format); $i++) {
            $character = $format[$i];

            // Check if we can replace it with a translated version.
            if (in_array($character, ['D', 'l', 'F', 'M'])) {
                // Check escaped characters.
                if ($i > 0 and $format[$i - 1] == '\\') {
                    continue;
                }

                switch ($character) {
                    case 'D':
                        $key = parent::format('l');
                        break;
                    case 'M':
                        $key = parent::format('F');
                        break;
                    default:
                        $key = parent::format($character);
                }

                // The original result.
                $original = parent::format($character);

                // Translate.
                $lang = $this->getTranslator();

                // For declension support, we need to check if the month is lead by a day number.
                // If so, we will use the second translation choice if it is available.
                if (in_array($character, ['F', 'M'])) {
                    $choice = preg_match('#[dj][ .]*$#', substr($format, 0, $i)) ? 1 : 0;

                    $translated = $lang->transChoice(mb_strtolower($key), $choice);
                } else {
                    $translated = $lang->trans(mb_strtolower($key));
                }

                // Short notations.
                if (in_array($character, ['D', 'M'])) {
                    $toTranslate = mb_strtolower($original);
                    $shortTranslated = $lang->trans($toTranslate);

                    if ($shortTranslated === $toTranslate) {
                        // use the first 3 characters as short notation
                        $translated = mb_substr($translated, 0, 3);
                    } else {
                        // use translated version
                        $translated = $shortTranslated;
                    }
                }

                // Add to replace list.
                if ($translated and $original != $translated) {
                    $replace[$original] = $translated;
                }
            }
        }

        // Replace translations.
        if ($replace) {
            return str_replace(array_keys($replace), array_values($replace), parent::format($format));
        }

        return parent::format($format);
    }

    /**
     * Gets the timespan between this date and another date.
     *
     * @param  Date $time
     * @param  string|DateTimeZone $timezone
     * @return int
     */
    public function timespan($time = null, $timezone = null)
    {
        // Get translator
        $lang = $this->getTranslator();

        // Create Date instance if needed
        if (!$time instanceof static) {
            $time = Date::parse($time, $timezone);
        }

        $units = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        // Get DateInterval and cast to array
        $interval = (array) $this->diff($time);

        // Get weeks
        $interval['w'] = (int) ($interval['d'] / 7);
        $interval['d'] = $interval['d'] % 7;

        // Get ready to build
        $str = [];

        // Loop all units and build string
        foreach ($units as $k => $unit) {
            if ($interval[$k]) {
                $str[] = $lang->transChoice($unit, $interval[$k], [':count' => $interval[$k]]);
            }
        }

        return implode(', ', $str);
    }

    /**
     * Adds an amount of days, months, years, hours, minutes and seconds to a Date object.
     *
     * @param DateInterval|string $interval
     * @return Date|bool
     */
    public function add($interval)
    {
        if (is_string($interval)) {
            // Check for ISO 8601
            if (strtoupper(substr($interval, 0, 1)) == 'P') {
                $interval = new DateInterval($interval);
            } else {
                $interval = DateInterval::createFromDateString($interval);
            }
        }

        return parent::add($interval) ? $this : false;
    }

    /**
     * Subtracts an amount of days, months, years, hours, minutes and seconds from a DateTime object.
     *
     * @param DateInterval|string $interval
     * @return Date|bool
     */
    public function sub($interval)
    {
        if (is_string($interval)) {
            // Check for ISO 8601
            if (strtoupper(substr($interval, 0, 1)) == 'P') {
                $interval = new DateInterval($interval);
            } else {
                $interval = DateInterval::createFromDateString($interval);
            }
        }

        return parent::sub($interval) ? $this : false;
    }

    /**
     * @inheritdoc
     */
    public static function getLocale()
    {
        return static::getTranslator()->getLocale();
    }

    /**
     * @inheritdoc
     */
    public static function setLocale($locale)
    {
        // Use RFC 5646 for filenames.
        $files = array_unique([
            str_replace('_', '-', $locale),
            static::getLanguageFromLocale($locale),
            str_replace('_', '-', static::getFallbackLocale()),
            static::getLanguageFromLocale(static::getFallbackLocale()),
        ]);

        $found = false;

        foreach ($files as $file) {
            $resource = __DIR__.'/Lang/'.$file.'.php';

            if (file_exists($resource)) {
                $found = true;
                $locale = $file;
                break;
            }
        }

        if (!$found) {
            return;
        }

        // Symfony locale format.
        $locale = str_replace('-', '_', $locale);

        // Set locale and load translations.
        static::getTranslator()->setLocale($locale);
        static::getTranslator()->addResource('array', require $resource, $locale);
    }

    /**
     * Set the fallback locale.
     *
     * @param  string $locale
     */
    public static function setFallbackLocale($locale)
    {
        static::$fallbackLocale = $locale;
        static::getTranslator()->setFallbackLocales([$locale]);
    }

    /**
     * Get the fallback locale.
     *
     * @return string
     */
    public static function getFallbackLocale()
    {
        return static::$fallbackLocale;
    }

    /**
     * @inheritdoc
     */
    public static function getTranslator()
    {
        if (static::$translator === null) {
            static::$translator = new Translator('en');
            static::$translator->addLoader('array', new ArrayLoader());
            static::setLocale('en');
        }

        return static::$translator;
    }

    /**
     * @inheritdoc
     */
    public static function setTranslator(TranslatorInterface $translator)
    {
        static::$translator = $translator;
    }

    /**
     * Translate a locale based time string to its english equivalent.
     *
     * @param  string $time
     * @return string
     */
    public static function translateTimeString($time)
    {
        // Don't run translations for english.
        if (static::getLocale() == 'en') {
            return $time;
        }

        // All the language file items we can translate.
        $keys = [
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];

        // Get all the language lines of the current locale.
        $all = static::getTranslator()->getCatalogue()->all();
        $terms = array_intersect_key($all['messages'], array_flip((array) $keys));

        // Split terms with a | sign.
        foreach ($terms as $i => $term) {
            if (strpos($term, '|') === false) {
                continue;
            }

            // Split term options.
            $options = explode('|', $term);

            // Remove :count and {count} placeholders.
            $options = array_map(function ($option) {
                $option = trim(str_replace(':count', '', $option));
                $option = preg_replace('/({\d+(,(\d+|Inf))?}|\[\d+(,(\d+|Inf))?\])/', '', $option);

                return $option;
            }, $options);

            $terms[$i] = $options;
        }

        // Replace the localized words with English words.
        $translated = $time;
        foreach ($terms as $english => $localized) {
            $translated = str_ireplace($localized, $english, $translated);
        }

        return $translated;
    }

    /**
     * Get the language portion of the locale.
     *
     * @param string $locale
     * @return string
     */
    public static function getLanguageFromLocale($locale)
    {
        $parts = explode('_', str_replace('-', '_', $locale));

        return $parts[0];
    }
}
