<?php

namespace Henri1i\CircuitBreaker;

use Henri1i\CircuitBreaker\Domain\CircuitBreakerConfig;
use Henri1i\CircuitBreaker\Domain\StateRepository;
use Henri1i\CircuitBreaker\Domain\Key;

class CircuitBreaker
{
    private CircuitBreakerConfig $config;
    private StateRepository $state;

    public function __construct(
        private readonly string $service,
    ) {
        $this->config = new CircuitBreakerConfig();

        /** @var StateRepository $state */
        $state = app(StateRepository::class);
        $this->state = $state;
    }

    public function isAvailable(): bool
    {
        return ! $this->isOpen();
    }

    public function handleFailure(): void
    {
        if ($this->isHalfOpen()) {
            $this->openCircuit();

            return;
        }

        $this->incrementErrors();

        if ($this->reachedErrorThreshold() && ! $this->isOpen()) {
            $this->openCircuit();
        }
    }

    public function handleSuccess(): void
    {
        if (! $this->isHalfOpen()) {
            $this->reset();

            return;
        }

        $this->incrementSuccesses();

        if ($this->reachedSuccessThreshold()) {
            $this->reset();
        }
    }

    private function isOpen(): bool
    {
        return (bool) $this->state->get($this->getKey(Key::OPEN), 0);
    }

    private function isHalfOpen(): bool
    {
        $isHalfOpen = (bool) $this->state->get($this->getKey(Key::HALF_OPEN), 0);

        return ! $this->isOpen() && $isHalfOpen;
    }

    private function reachedErrorThreshold(): bool
    {
        $failures = $this->getErrorsCount();

        return $failures >= $this->config->errorThreshold;
    }

    private function reachedSuccessThreshold(): bool
    {
        $successes = $this->getSuccessesCount();

        return $successes >= $this->config->successThreshold;
    }

    private function incrementErrors(): void
    {
        $key = $this->getKey(Key::ERRORS);

        if (! $this->state->get($key)) {
            $this->state->put($key, 1, $this->config->timeoutWindow);
        }

        $this->state->increment($key);
    }

    private function incrementSuccesses(): void
    {
        $key = $this->getKey(Key::SUCCESSES);

        if (! $this->state->get($key)) {
            $this->state->put($key, 1, $this->config->timeoutWindow);
        }

        $this->state->increment($key);
    }

    private function reset(): void
    {
        foreach (Key::cases() as $key) {
            $this->state->delete($this->getKey($key));
        }
    }

    private function setOpenCircuit(): void
    {
        $this->state->put(
            $this->getKey(Key::OPEN),
            time(),
            $this->config->errorTimeout
        );
    }

    private function setHalfOpenCircuit(): void
    {
        $this->state->put(
            $this->getKey(Key::HALF_OPEN),
            time(),
            $this->config->errorTimeout + $this->config->halfOpenTimeout
        );
    }

    private function getErrorsCount(): int
    {
        return (int) $this->state->get(
            $this->getKey(Key::ERRORS),
            0
        );
    }

    private function getSuccessesCount(): int
    {
        return (int) $this->state->get(
            $this->getKey(Key::SUCCESSES),
            0
        );
    }

    private function openCircuit(): void
    {
        $this->setOpenCircuit();
        $this->setHalfOpenCircuit();
    }

    private function getKey(?Key $key): string
    {
        return "circuit-breaker:{$this->service}:{$key?->value}";
    }
}