<?php

namespace rest;

interface iResourceModel {

    public function toArray();

    public function __get($property);

    public function __set($property, $value);
    
    public function __isset($property);
}
