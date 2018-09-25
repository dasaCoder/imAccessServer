<?php

namespace App\Controllers;

class Controller 
{
    protected $container;

    public function __construct($c)
    {
        $this->container = $c;
    }

    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }

    public function setResponse($error, $msg = null, $data = null)
    {
        $array = array();
        $array['error'] = boolval($error);

        if ($msg) {
            $array['msg'] = $msg;
        }

        if ($data) {
            foreach ($data as $key => $value) {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}