<?php

namespace EloquentSessionHandler;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;

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
        $instance = new $model;

        parent::__construct($instance->getConnection(), $instance->getTable(), $minutes, $container);
    }

    /**
     * Get a fresh query builder instance for the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        return (new $this->model)->newQuery();
    }
}
