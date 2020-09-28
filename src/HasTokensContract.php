<?php

namespace Jundayw\LaravelTokens;

interface HasTokensContract
{
    public function getJWTIdentifier();

    public function getJWTCustomClaims();
}