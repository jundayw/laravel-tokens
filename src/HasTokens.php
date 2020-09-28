<?php

namespace Jundayw\LaravelTokens;

trait HasTokens
{
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
}