<?php

namespace Aws\Configuration;

/**
 * Resolves configuration values from, in order of precedence:
 *   1. An AWS_-prefixed environment variable.
 *   2. The shared config file (AWS_CONFIG_FILE, defaulting to ~/.aws/config).
 *   3. A caller-supplied default.
 */
class ConfigurationResolver
{
    const ENV_PROFILE = 'AWS_PROFILE';
    const ENV_CONFIG_FILE = 'AWS_CONFIG_FILE';

    const DEFAULT_PROFILE = 'default';

    /** Prefix AWS applies to every non-default profile section in the config file. */
    const PROFILE_PREFIX = 'profile ';

    public static $envPrefix = 'AWS_';

    /**
     * @param string $key          Key to look up in the environment / config file.
     * @param mixed  $defaultValue Returned when nothing else resolves.
     * @param string $expectedType Type to coerce the resolved value to.
     * @param array  $config       Options: 'ini_resolver_options',
     *                             'use_aws_shared_config_files'.
     *
     * @return mixed
     */
    public static function resolve($key, $defaultValue, $expectedType, $config = [])
    {
        $envValue = self::env($key, $expectedType);
        if ($envValue !== null) {
            return $envValue;
        }

        $useSharedConfig = $config['use_aws_shared_config_files'] ?? true;
        if ($useSharedConfig !== false) {
            $iniValue = self::ini(
                $key,
                $expectedType,
                null,
                null,
                $config['ini_resolver_options'] ?? []
            );
            if ($iniValue !== null) {
                return $iniValue;
            }
        }

        return $defaultValue;
    }

    /**
     * Resolves a value from an AWS_-prefixed environment variable.
     *
     * @param string $key
     * @param string $expectedType
     *
     * @return mixed|null
     */
    public static function env($key, $expectedType = 'string')
    {
        $envValue = getenv(self::$envPrefix . strtoupper($key));

        // false => variable is unset; '' => set but empty. Both resolve to null.
        // A literal "0" is a valid value and must NOT be treated as empty.
        if ($envValue === false || $envValue === '') {
            return null;
        }

        return $expectedType
            ? self::convertType($envValue, $expectedType)
            : $envValue;
    }

    /**
     * Resolves a value from the shared config file.
     *
     * @param string      $key
     * @param string      $expectedType
     * @param string|null $profile  Profile to read. Defaults to AWS_PROFILE,
     *                              then "default".
     * @param string|null $filename Config file path. Defaults to AWS_CONFIG_FILE,
     *                              then ~/.aws/config.
     * @param array       $options  Subsection lookup options
     *                             ('section', 'subsection', 'key').
     *
     * @return mixed|null
     */
    public static function ini(
        $key,
        $expectedType,
        $profile = null,
        $filename = null,
        $options = []
    ) {
        $filename = $filename ?: self::getDefaultConfigFilename();
        $profile = $profile ?: (getenv(self::ENV_PROFILE) ?: self::DEFAULT_PROFILE);

        if (!@is_readable($filename)) {
            return null;
        }

        // INI_SCANNER_TYPED coerces bool/int/float/null at parse time; a value
        // left empty (key =) still comes back as an empty string. convertType()
        // normalizes both these typed values and the raw strings from env().
        $data = @\Aws\parse_ini_file($filename, true, INI_SCANNER_TYPED);
        if ($data === false) {
            return null;
        }

        if (isset($options['section'], $options['subsection'], $options['key'])) {
            return self::retrieveValueFromIniSubsection(
                $data,
                $profile,
                $filename,
                $expectedType,
                $options
            );
        }

        $section = self::getProfileSection($data, $profile);
        if ($section === null || !isset($section[$key])) {
            return null;
        }

        return self::convertType($section[$key], $expectedType);
    }

    /**
     * Returns the config-file section for a profile, accounting for the
     * "profile " prefix AWS applies to every non-default profile.
     *
     * For a non-default profile "foo" the lookup order is:
     *   1. [profile foo]  (canonical AWS form)
     *   2. [foo]          (lenient fallback for hand-written files)
     *
     * The default profile is conventionally written as [default], with
     * [profile default] tolerated as a fallback.
     *
     * @param array  $data
     * @param string $profile
     *
     * @return array|null
     */
    private static function getProfileSection(array $data, $profile)
    {
        if ($profile === self::DEFAULT_PROFILE) {
            return $data[self::DEFAULT_PROFILE]
                ?? $data[self::PROFILE_PREFIX . self::DEFAULT_PROFILE]
                ?? null;
        }

        return $data[self::PROFILE_PREFIX . $profile]
            ?? $data[$profile]
            ?? null;
    }

    /**
     * Resolves a value nested in a referenced section (e.g. a profile that
     * points at a "services" section via `services = my-services`).
     *
     * @param array  $data
     * @param string $profile
     * @param string $filename
     * @param string $expectedType
     * @param array  $options
     *
     * @return mixed|null
     */
    private static function retrieveValueFromIniSubsection(
        array $data,
        $profile,
        $filename,
        $expectedType,
        array $options
    ) {
        $profileData = self::getProfileSection($data, $profile);
        $section = $options['section'];

        // The profile must name a referenced section, and that section must exist.
        if ($profileData === null || !isset($profileData[$section])) {
            return null;
        }

        $referencedSection = "{$section} {$profileData[$section]}";
        if (!isset($data[$referencedSection])) {
            return null;
        }

        $subsections = \Aws\parse_ini_section_with_subsections(
            $filename,
            $referencedSection
        );

        $subsection = $options['subsection'];
        $subKey = $options['key'];
        if (!isset($subsections[$subsection][$subKey])) {
            return null;
        }

        return self::convertType($subsections[$subsection][$subKey], $expectedType);
    }

    /**
     * Gets the environment's HOME directory if available.
     *
     * @return string|null
     */
    private static function getHomeDir()
    {
        // Linux / Unix-like systems.
        if ($homeDir = getenv('HOME')) {
            return $homeDir;
        }

        // Windows hosts.
        $homeDrive = getenv('HOMEDRIVE');
        $homePath = getenv('HOMEPATH');

        return ($homeDrive && $homePath) ? $homeDrive . $homePath : null;
    }

    /**
     * Gets the config file location from the environment, falling back to the
     * AWS default location.
     *
     * @return string
     */
    private static function getDefaultConfigFilename()
    {
        return getenv(self::ENV_CONFIG_FILE)
            ?: self::getHomeDir() . '/.aws/config';
    }

    /**
     * Coerces a value to the expected type. The value may be a raw string
     * (from env()) or already typed by INI_SCANNER_TYPED (from ini()).
     * Unrecognized values are returned unchanged.
     *
     * @param mixed  $value
     * @param string $type
     *
     * @return mixed
     */
    private static function convertType(mixed $value, string $type): mixed
    {
        // INI_SCANNER_TYPED may already yield a bool/int for keyword or numeric
        // values; env() always passes a string. Each arm fast-paths the
        // already-typed case and delegates conversion otherwise. TYPED may also
        // return int/float/bool for numeric or keyword 'string' values, so the
        // string arm casts those back to string as it did under NORMAL.
        return match ($type) {
            'bool'   => is_bool($value) ? $value : self::toBool($value),
            'int'    => is_int($value) ? $value : self::toInt($value),
            'string' => is_string($value) ? $value : (string) $value,
            default  => $value,
        };
    }

    /**
     * Coerces a non-bool value (typically a string from env()) to bool.
     * \Aws\boolean_value() returns null when it can't interpret the value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private static function toBool(mixed $value): mixed
    {
        if ($value === '') {
            return false;
        }
        return \Aws\boolean_value($value) ?? $value;
    }

    /**
     * Coerces a non-int value (typically a string from env()) to int. Uses
     * !== false on filter_var() so a valid "0" is not dropped; if the value
     * is not a valid int, the original is returned unchanged.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private static function toInt(mixed $value): mixed
    {
        if ($value === '') {
            return 0;
        }
        $int = filter_var($value, FILTER_VALIDATE_INT);
        return $int !== false ? $int : $value;
    }
}
