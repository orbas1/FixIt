<?php

namespace App\Models;

class Customer extends User
{
    protected $table = "users";

    protected $primaryKey = 'id';
}
