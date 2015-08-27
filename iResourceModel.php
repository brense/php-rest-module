<?php

namespace rest;

interface iResourceModel {

    public function toArray();

    public function __get();

    public function __set();
    
    public function __isset();
}
