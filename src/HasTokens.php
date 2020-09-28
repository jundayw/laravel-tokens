<?php

namespace Jundayw\LaravelTokens;

trait HasTokens
{
    public $claims = [
//        'iss' => 'CLIENT',
//        'sub' => 'APP',
//        'aud' => 'PASSWORD',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return $this->claims;
    }
}