<?php

namespace SamuelAndrey\Belajar\PHP\MVC\Middleware;

interface Middleware
{
    function before(): void;
}