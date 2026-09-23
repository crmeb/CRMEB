---
title: Implementation
weight: 20
---

## Import the trait

In your Composer plugin, import the `ConfigurablePlugin` trait.

{{< highlight php "hl_lines=6" >}}
use Composer\Plugin\PluginInterface;
use cweagans\Composer\ConfigurablePlugin;

class YourPlugin implements PluginInterface
{
    use ConfigurablePlugin;
    
    [...]
}
{{< /highlight >}}


## Declare configuration values

Next, in the `activate` function of your plugin, declare your configuration schema.

{{< highlight php "hl_lines=12-34" >}}
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use cweagans\Composer\ConfigurablePlugin;

class YourPlugin implements PluginInterface
{
    use ConfigurablePlugin;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->configuration = [
            // You can have any number of blocks like this one. Each key must be unique.
            'my-string-config' => [
                // Required: this allows ConfigurablePlugin to determine values from environment variables.
                // Allowed values: string, int, bool, list
                'type' => 'string',
                // Required: In the event that the users of your plugin don't bother to provide configuration, you need to provide reasonable defaults.
                // The value must match the configuration value type. (further examples below)
                'default' => 'somevalue',          
            ],
            'my-int-config' => [
                'type' => 'int',
                'default' => 123,          
            ],
            'my-bool-config' => [
                'type' => 'bool',
                'default' => false,          
            ],
            'my-list-config' => [
                'type' => 'list',
                'default' => ['somevalue'],          
            ],
        ];
    }

    [...]    
}
{{< /highlight >}}

## Tell the library to load config values

{{< highlight php "hl_lines=12-34" >}}
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use cweagans\Composer\ConfigurablePlugin;

class YourPlugin implements PluginInterface
{
    use ConfigurablePlugin;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->configuration = [...];
        
        // The second argument here can be an arbitrary string, but should be unique across
        // all Composer plugins. This string is used for loading configuration from your
        // composer.json and for constructing the name of the environment variables to
        // check for configuration values.
        $this->configure($composer->getPackage()->getExtraA(), 'unique-key-for-your-plugin');
    }

    [...]    
}
{{< /highlight >}}


## Provide configuration

Given the previous example configuration schema, you can provide configuration in `composer.json` like so:

```json
{
    "extra": {
        "unique-key-for-your-plugin": {
            "my-string-config": "some user-provided value",
            "my-int-config": 12345,
            "my-bool-config": true,
            "my-list-config": ["a value", "another value"]
        }
    }
}
```

You can also provide configuration through environment variables set before running Composer:

```shell
export UNIQUE_KEY_FOR_YOUR_PLUGIN_MY_STRING_CONFIG="some user-provided value"
export UNIQUE_KEY_FOR_YOUR_PLUGIN_MY_INT_CONFIG=1234567
export UNIQUE_KEY_FOR_YOUR_PLUGIN_MY_BOOL_CONFIG=false
export UNIQUE_KEY_FOR_YOUR_PLUGIN_MY_LIST_CONFIG=asdf,sdfg,dfgh
composer install
```

## Retrieve configuration values

Once all of your configuration has been wired up, the last step is to use your configuration value somewhere. You can do so anywhere in your Composer plugin by calling the `getConfig` method:

```php
$this->getConfig('my-string-config');
$this->getConfig('my-int-config');
$this->getConfig('my-bool-config');
$this->getConfig('my-list-config');
```
