<?php

namespace verbb\auth\clients\vend\provider\vendapi;

abstract class VendObject
{
    /**
     * @var mixed
     */
    protected $vend;
    /**
     * @var array
     */
    protected $vendObjectProperties = array();
    /**
     * @var array
     */
    protected $initialObjectProperties = array();

    /**
     * @param $data
     * @param null $v
     */
    public function __construct($data = null, &$v = null)
    {
        $this->vend = $v;
        if ($data) {
            foreach ($data as $key => $value) {
                $this->vendObjectProperties[$key] = $value;
            }
            $this->initialObjectProperties = $this->vendObjectProperties;
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->vendObjectProperties[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->vendObjectProperties)) {
            return $this->vendObjectProperties[$key];
        }

        return null;
    }

    /**
     * @param $key
     */
    public function __isset($key)
    {
        return isset($this->vendObjectProperties[$key]);
    }

    /**
     * @param $key
     */
    public function __unset($key)
    {
        unset($this->vendObjectProperties[$key]);
    }

    public function clear()
    {
        $this->vendObjectProperties = array();
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return $this->vendObjectProperties;
    }

    /**
     * will return an array of all changed properties and the id
     * return array
     */
    public function saveArray()
    {
        // only output the changed properties
        $output = $this->vendObjectProperties;
        foreach ($output as $key => $value) {
            if ($key != 'id' &&
                isset($this->initialObjectProperties[$key]) &&
                $value == $this->initialObjectProperties[$key] ) {
                unset($output[$key]);
            }
        }
        return $output;
    }
}
