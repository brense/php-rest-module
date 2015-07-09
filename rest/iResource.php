<?php

namespace rest;

interface iResource {
    
    public function getController();
    public function toArray();
    
}