<?php

namespace verbb\auth\clients\gotowebinar\resultset;

use ArrayObject;
use JsonSerializable;
use Countable;
use Serializable;
use ArrayAccess;
use IteratorAggregate;

interface ResultSetInterface extends IteratorAggregate, ArrayAccess, Serializable, Countable, JsonSerializable
{

    /**
     * Return just the data, free of pagination setups and other stuffs.
     */
    public function getData(): ArrayObject;
}
