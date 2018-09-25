<?php

namespace App\Controllers;

//use phpDocumentor\Reflection\Types\Null_;


class ParentController extends Controller{
    public function __construct($c)
    {
        parent::__construct($c);
    }

    public function login(){
        $this->logger->info("login app");
    }

}
