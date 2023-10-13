<?php

namespace verbb\auth\clients\steemconnect\common\http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Class Request.
 *
 * Implements a singleton around symfony request which is created from global variables.
 */
class Request
{
    /**
     * @var Request|null Current request instance.
     */
    protected static ?Request $instance = null;

    /**
     * Protected CurrentRequest constructor.
     */
    protected function __construct()
    {
        //
    }

    /**
     * Current request instance retriever methods.
     *
     * @return Request|SymfonyRequest|null
     */
    public static function current(): Request|SymfonyRequest|null
    {
        // this is a dummy call.
        $dummy = new self();

        // assign a new instance only if none is present.
        if (!self::$instance) {
            self::$instance = SymfonyRequest::createFromGlobals();
        }

        // return the instance value.
        return self::$instance;
    }
}
