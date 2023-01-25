<?php

namespace Henri1i\CircuitBreaker\Infra;

use Henri1i\CircuitBreaker\Domain\StateRepository;
use Henri1i\CircuitBreaker\Infra\Repositories\StateCacheRepository;
use Illuminate\Support\ServiceProvider;

class CircuitBreakerServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->bind(StateRepository::class, StateCacheRepository::class);
    }
}
