<?php

class Scrn_Request {
    public $id;
    public $url;
    public $requested;
    public $processed;
    public $build_time;
    public $built;
    public $options;


    public static function fromArray(array $a) {
        $instance = new static();
        foreach ($a as $key => $value) {
            $instance->$key = $value;
        }
        return $instance;
    }

}
