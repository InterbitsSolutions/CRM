<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class FraudIP extends Model
{
    protected $table = "fraud_ip";
    public $timestamps = true;
}
