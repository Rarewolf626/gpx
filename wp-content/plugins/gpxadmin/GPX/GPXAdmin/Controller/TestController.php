<?php

namespace GPX\GPXAdmin\Controller;

class TestController {
    public function index( string $name = 'World' ) {
       return 'Hello ' . $name;
    }
}
