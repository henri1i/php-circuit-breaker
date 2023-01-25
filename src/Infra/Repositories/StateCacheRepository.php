<?php

namespace Henri1i\CircuitBreaker\Infra\Repositories;

use Henri1i\CircuitBreaker\Domain\StateRepository;
use Illuminate\Support\Facades\Cache;

class StateCacheRepository implements StateRepository
{

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($key, $default);
    }

    public function put(string $key, int $value, int $ttl): void
    {
        Cache::put($key, $value, $ttl);
    }

    public function increment(string $key): void
    {
        Cache::increment($key);
    }

    public function delete(string $key): void
    {
        Cache::delete($key);
    }
}