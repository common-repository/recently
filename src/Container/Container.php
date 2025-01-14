<?php
/**
 * A Simple Dependency Injector Container (DIC).
 *
 * Largely based on Carl Alexander's Dependency Injection Container.
 * @link https://carlalexander.ca/dependency-injection-wordpress/
 */

namespace Recently\Container;

class Container implements \ArrayAccess
{
    /**
     * Values stored inside the container.
     *
     * @var array
     */
    private $values;

    /**
     * Constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Configure the container using the given container configuration objects.
     *
     * @param array $configurations
     */
    public function configure(array $configurations)
    {
        foreach ($configurations as $configuration) {
            if ( $configuration instanceof ContainerConfigurationInterface ) {
                $configuration->modify($this);
            }
        }
    }

    /**
     * Checks if there's a value in the container for the given key.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key) : bool /** @TODO: starting PHP 8.0 $key can be declared as mixed $key, see https://www.php.net/manual/en/language.types.declarations.php */
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Sets a value inside of the container.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value) : void /** @TODO: starting PHP 8.0 $key and $value can be declared as mixed $key, mixed $value */
    {
        $this->values[$key] = $value;
    }

    /**
     * Unset the value in the container for the given key.
     *
     * @param mixed $key
     */
    public function offsetUnset($key) : void /** @TODO: starting PHP 8.0 $key can be declared as mixed $key */
    {
        unset($this->values[$key]);
    }

    /**
     * Get a value from the container.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key) /** @TODO: starting PHP 8.0 $key can be declared as mixed $key */
    {
        if ( ! $this->offsetExists($key) ) {
            throw new \InvalidArgumentException(sprintf('Container doesn\'t have a value stored for the "%s" key.', $key));
        }
        return $this->values[$key] instanceof \Closure ? $this->values[$key]($this) : $this->values[$key];
    }

    /**
     * Creates a closure used for creating a service using the given callable.
     *
     * @param \Closure $closure
     *
     * @return \Closure
     */
    public function service(\Closure $closure)
    {
        return function(Container $container) use ($closure) {
            static $object;

            if ( null === $object ) {
                $object = $closure($container);
            }

            return $object;
        };
    }
}

