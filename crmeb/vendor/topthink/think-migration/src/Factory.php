<?php

namespace think\migration;

use ArrayAccess;
use Faker\Generator as Faker;

class Factory implements ArrayAccess
{

    /**
     * The model definitions in the container.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * The registered model states.
     *
     * @var array
     */
    protected $states = [];

    /**
     * The registered after making callbacks.
     *
     * @var array
     */
    protected $afterMaking = [];

    /**
     * The registered after creating callbacks.
     *
     * @var array
     */
    protected $afterCreating = [];

    /**
     * The Faker instance for the builder.
     *
     * @var Faker
     */
    protected $faker;

    /**
     * Create a new factory instance.
     *
     * @param Faker $faker
     * @return void
     */
    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Define a class with a given short-name.
     *
     * @param string   $class
     * @param string   $name
     * @param callable $attributes
     * @return $this
     */
    public function defineAs(string $class, string $name, callable $attributes)
    {
        return $this->define($class, $attributes, $name);
    }

    /**
     * Define a class with a given set of attributes.
     *
     * @param string   $class
     * @param callable $attributes
     * @param string   $name
     * @return $this
     */
    public function define(string $class, callable $attributes, string $name = 'default')
    {
        $this->definitions[$class][$name] = $attributes;

        return $this;
    }

    /**
     * Define a state with a given set of attributes.
     *
     * @param string         $class
     * @param string         $state
     * @param callable|array $attributes
     * @return $this
     */
    public function state(string $class, string $state, $attributes)
    {
        $this->states[$class][$state] = $attributes;

        return $this;
    }

    /**
     * Define a callback to run after making a model.
     *
     * @param string   $class
     * @param callable $callback
     * @param string   $name
     * @return $this
     */
    public function afterMaking(string $class, callable $callback, string $name = 'default')
    {
        $this->afterMaking[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after making a model with given state.
     *
     * @param string   $class
     * @param string   $state
     * @param callable $callback
     * @return $this
     */
    public function afterMakingState(string $class, string $state, callable $callback)
    {
        return $this->afterMaking($class, $callback, $state);
    }

    /**
     * Define a callback to run after creating a model.
     *
     * @param string   $class
     * @param callable $callback
     * @param string   $name
     * @return $this
     */
    public function afterCreating(string $class, callable $callback, string $name = 'default')
    {
        $this->afterCreating[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after creating a model with given state.
     *
     * @param string   $class
     * @param string   $state
     * @param callable $callback
     * @return $this
     */
    public function afterCreatingState(string $class, string $state, callable $callback)
    {
        return $this->afterCreating($class, $callback, $state);
    }

    /**
     * Create an instance of the given model and persist it to the database.
     *
     * @param string $class
     * @param array  $attributes
     * @return mixed
     */
    public function create(string $class, array $attributes = [])
    {
        return $this->of($class)->create($attributes);
    }

    /**
     * Create an instance of the given model and type and persist it to the database.
     *
     * @param string $class
     * @param string $name
     * @param array  $attributes
     * @return mixed
     */
    public function createAs(string $class, string $name, array $attributes = [])
    {
        return $this->of($class, $name)->create($attributes);
    }

    /**
     * Create an instance of the given model.
     *
     * @param string $class
     * @param array  $attributes
     * @return mixed
     */
    public function make(string $class, array $attributes = [])
    {
        return $this->of($class)->make($attributes);
    }

    /**
     * Create an instance of the given model and type.
     *
     * @param string $class
     * @param string $name
     * @param array  $attributes
     * @return mixed
     */
    public function makeAs(string $class, string $name, array $attributes = [])
    {
        return $this->of($class, $name)->make($attributes);
    }

    /**
     * Get the raw attribute array for a given named model.
     *
     * @param string $class
     * @param string $name
     * @param array  $attributes
     * @return array
     */
    public function rawOf(string $class, string $name, array $attributes = [])
    {
        return $this->raw($class, $attributes, $name);
    }

    /**
     * Get the raw attribute array for a given model.
     *
     * @param string $class
     * @param array  $attributes
     * @param string $name
     * @return array
     */
    public function raw(string $class, array $attributes = [], string $name = 'default')
    {
        return array_merge(
            call_user_func($this->definitions[$class][$name], $this->faker), $attributes
        );
    }

    /**
     * Create a builder for the given model.
     *
     * @param string $class
     * @param string $name
     * @return FactoryBuilder
     */
    public function of(string $class, string $name = 'default')
    {
        return new FactoryBuilder(
            $class, $name, $this->definitions, $this->states,
            $this->afterMaking, $this->afterCreating, $this->faker
        );
    }

    /**
     * Load factories from path.
     *
     * @param string $path
     * @return $this
     */
    public function load(string $path)
    {
        $factory = $this;

        if (is_dir($path)) {
            foreach (glob($path . '*.php') as $file) {
                require $file;
            }
        }

        return $factory;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->definitions[$offset]);
    }

    /**
     * Get the value of the given offset.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->make($offset);
    }

    /**
     * Set the given offset to the given value.
     *
     * @param string   $offset
     * @param callable $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->define($offset, $value);
    }

    /**
     * Unset the value at the given offset.
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->definitions[$offset]);
    }

}
