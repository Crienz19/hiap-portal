<?php

namespace App\Actions;

use App\UserProgram;

class CreateUserProgram 
{
    public function execute($data)
    {
        UserProgram::create($data);
    }
}