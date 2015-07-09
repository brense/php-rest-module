<?php

namespace rest;

/**
 * Resource interface
 */
interface iResource {
    
    public function getController();
    public function toArray();
    
}