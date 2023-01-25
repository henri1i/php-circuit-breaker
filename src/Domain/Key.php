<?php

namespace Henri1i\CircuitBreaker\Domain;

enum Key: string
{
    case OPEN = 'open';
    case HALF_OPEN = 'half-open';
    case ERRORS = 'errors';
    case SUCCESSES = 'successes';
}
