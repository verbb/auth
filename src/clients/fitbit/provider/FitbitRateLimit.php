<?php

namespace verbb\auth\clients\fitbit\provider;

use Psr\Http\Message\ResponseInterface;

class FitbitRateLimit
{
    private string $retryAfter;
    private string $limit;
    private string $remaining;
    private string $reset;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 429) {
            $this->retryAfter = $response->getHeader('Retry-After')[0];
        }
        $this->limit = $response->getHeader('Fitbit-Rate-Limit-Limit')[0];
        $this->remaining = $response->getHeader('Fitbit-Rate-Limit-Remaining')[0];
        $this->reset = $response->getHeader('Fitbit-Rate-Limit-Reset')[0];
    }

    /**
     * In the event the request is over the rate limit, Fitbit returns the number
     * of seconds until the rate limit is reset and the request should be retried.
     *
     * @return String Number of seconds until request should be retried.
     */
    public function getRetryAfter(): string
    {
        return $this->retryAfter;
    }

    /**
     * @return String The quota number of calls.
     */
    public function getLimit(): string
    {
        return $this->limit;
    }

    /**
     * @return String The number of calls remaining before hitting the rate limit.
     */
    public function getRemaining(): string
    {
        return $this->remaining;
    }

    /**
     * @return String The number of seconds until the rate limit resets.
     */
    public function getReset(): string
    {
        return $this->reset;
    }
}
