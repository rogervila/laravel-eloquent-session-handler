<?php

namespace EloquentSessionHandler;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Contracts\Container\Container;

class Handler extends DatabaseSessionHandler
{
    /** @var string */
    protected $model;

    /**
     * Create a new Eloquent session handler instance.
     *
     * @param  string  $model
     * @param  int  $minutes
     * @param  \Illuminate\Contracts\Container\Container|null  $container
     * @return void
     */
    public function __construct(string $model, int $minutes, Container $container = null)
    {
        $this->model = $model;

        $instance = $this->makeInstance();

        parent::__construct($instance->getConnection(), $instance->getTable(), $minutes, $container);
    }

    /**
     * Get a fresh query builder instance for the table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQuery()
    {
        return $this->makeInstance()->newQuery();
    }

    /**
     * Get a fresh query builder instance for the table.
     *
     * @return \EloquentSessionHandler\Session
     */
    protected function makeInstance()
    {
        /** @var \EloquentSessionHandler\Session */
        $instance = (new $this->model);

        return $instance;
    }
}
