<?php

namespace src;

use Tuupola\Middleware\JwtAuthentication;

function jwtAuth(): JwtAuthentication
{
    return new JwtAuthentication([
        'secret' => $_ENV['JWT_SECRET_KEY'],
        'attribute' => 'jwt'
    ]);
}