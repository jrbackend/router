<?php

namespace App\Middlewares;

class Guest
{
    public function handle(): bool
    {
        //your rule...
        return true;
    }
}