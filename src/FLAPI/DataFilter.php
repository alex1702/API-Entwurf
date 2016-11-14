<?php
namespace FLAPI;

/**
 * @class DataFilter
 * @author Alexander Jank <himself@alexanderjank.de>
 * @license GNU GPL v3.0
 * @package FLAPI
 */
class DataFilter {

    /**
     * @var array
     */
    private $data;

    /**
     * @param Array $data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * @param mixed $fkey
     * @return mixed
     */
    public function eachKey($fkey) {
        $rarr = [];
        foreach ($this->data as $key => $value) {
            $rarr[] = ((array) $value)[$fkey];
        }
        return $rarr;
    }

    /**
     * @param array $keys
     * @return mixed
     */
    public function only(Array $keys) {
        $output = [];
        foreach ($this->data as $k => $v) {
            if (in_array($k, $keys)) {
                $output[$k] = $v;
            }
        }
        return $output;
    }

    /**
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    public function doEachKey($method, $args) {
        $return = [];
        foreach ($this->data as $k => $v) {
            $me = new self((array) $v);
            $return[$k] = $me->{$method}($args);
            unset($me);
        }
        return $return;
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function stripKey($key) {
        $return = $this->data;
        if (isset($return[$key])) {
            unset($return[$key]);
        }

        return $return;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return array
     */
    public function getWithKey($key, $value) {
        foreach ($this->data as $data) {
            if (isset($data[$key]) && $data[$key] == $value) {
                return $data;
            }
        }
    }

    /**
     * @param mixed $key
     * @return array
     */
    public function getValEachKey($key) {
        $return = [];
        foreach ($this->data as $arr) {
            $return[] = $arr[$key];
        }
        return $return;
    }
}
