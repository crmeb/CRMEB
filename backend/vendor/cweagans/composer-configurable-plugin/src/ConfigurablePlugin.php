<?php

namespace cweagans\Composer;

/**
 * @file
 * Provides a generic way for Composer plugins to configure themselves.
 */

trait ConfigurablePlugin {

    /**
     * Holds information about configurable values.
     *
     * @var array
     */
    protected array $configuration = [];

    /**
     * Holds the 'extra' key from composer.json.
     *
     * @var array
     */
    protected array $extra;

    /**
     * Holds the name of the sub-key in extra to look for this plugin's config.
     *
     * @var string
     */
    protected string $pluginKey;

    /**
     * Set up the ConfigurablePlugin trait.
     *
     * @param array $extra
     *   The 'extra' section from composer.json.
     * @param string $pluginKey
     *   The subkey in extra to search for config. This will often be related to
     *   the plugin name. For instance, cweagans/composer-patches uses "patches-config".
     */
    public function configure(array $extra, string $pluginKey): void
    {
        $this->extra = $extra;
        $this->pluginKey = $pluginKey;
    }

    /**
     * Retrieve a specified configuration value.
     *
     * @param string $key
     *   The configuration key to get a value for.
     * @return string|int|bool|array
     *   The value of the config key.
     */
    public function getConfig($key): mixed
    {
        // Bail out early if we don't have any information from the plugin or
        // if the requested config key was not described to us.
        if (is_null($this->extra) || is_null($this->pluginKey)) {
            throw new \LogicException('You must call ConfigurablePlugin::configure() before attempting to retrieve a config value.');
        }
        if (!array_key_exists($key, $this->configuration)) {
            throw new \InvalidArgumentException('Config key ' . $key . ' was not declared in $configuration.');
        }

        // Start with the default value from configuration.
        $value = $this->configuration[$key]['default'];

        // If a value is set in composer.json, override the default.
        if (isset($this->extra[$this->pluginKey][$key])) {
            $value = $this->extra[$this->pluginKey][$key];
        }

        // If a value is set in the environment, override anything that we've
        // previously found.
        if (getenv($this->getEnvvarName($key)) !== FALSE) {
            $prevValue = $value;
            $value = getenv($this->getEnvvarName($key));
            switch ($this->configuration[$key]['type']) {
                case 'string':
                    // We don't need to do anything here. Envvars are all strings.
                    break;
                case 'int':
                    $value = (int)$value;
                    break;
                case 'bool':
                    $value = $this->castEnvvarToBool($value, $prevValue);
                    break;
                case 'list':
                    $value = $this->castEnvvarToList($value, $prevValue);
                    break;
            }
        }

        return $value;
    }

    /**
     * Convert a config key name into an environment var name.
     *
     * @param $key
     *   The key to convert.
     * @return string
     *   An envvar-ified version of $key
     */
    public function getEnvvarName($key): string
    {
        $key = $this->pluginKey . '_' . $key;
        $key = strtoupper($key);
        $key = str_replace('-', '_', $key);
        return $key;
    }

    /**
     * Get a boolean value from the environment.
     *
     * @param string $value
     *   The value retrieved from the environment.
     * @param bool $default
     *   The default value to use if we can't figure out what the user wants.
     *
     * @return bool
     */
    public function castEnvvarToBool($value, $default): bool
    {
        // Everything is strtolower()'d because that cuts the number of cases
        // to look for in half.
        $value = trim(strtolower($value));

        // If it looks false-y, return FALSE.
        if ($value == 'false' || $value == '0' || $value == 'no') {
            return FALSE;
        }

        // If it looks truth-y, return TRUE.
        if ($value == 'true' || $value == '1' || $value == 'yes') {
            return TRUE;
        }

        // Otherwise, just return the default value that we were given. Ain't
        // nobody got time to look for a million different ways of saying yes
        // or no.
        return $default;
    }

    /**
     * Get an array from the environment.
     *
     * @param string $value
     *   The value retrieved from the environment.
     * @param array $default
     *   The default value to use if we can't figure out what the user wants.
     *
     * @return array
     */
    public function castEnvvarToList($value, $default): array
    {
        // Trim any extra whitespace and then split the string on commas.
        $value = explode(',', trim($value));

        // Strip any empty values.
        $value = array_filter($value);

        // If we didn't get anything from the supplied value, better to just use the default.
        if (empty($value)) {
            return $default;
        }

        // Return the array.
        return $value;
    }
}
