<?php

namespace Henri1i\CircuitBreaker\Domain;

interface StateRepository
{
    public function get(string $key, mixed $default = null): mixed;

    public function put(string $key, int $value, int $ttl): void;

    public function increment(string $key): void;

    public function delete(string $key): void;
}