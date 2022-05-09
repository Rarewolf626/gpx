<?php

namespace GPX\Container;

use Illuminate\Container\Container;
use Psr\Container\ContainerInterface;
use Illuminate\Contracts\Container\Container as ContainerContract;

class LaravelContainer extends Container implements ContainerContract
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Class Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string $abstract
     *
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        if (!$this->bound($abstract)) {
            return $this->container->get($abstract);
        }

        return parent::resolve($abstract, $parameters);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string $abstract
     *
     * @return mixed
     */
    public function get($id)
    {
        return $this->make($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        if ($this->bound($id)) {
            return true;
        }

        return $this->container->has($id);
    }

    /**
     * Determine if the application is in maintenance mode.
     * @return bool
     */
    public function isDownForMaintenance() {
        return false;
    }
}
