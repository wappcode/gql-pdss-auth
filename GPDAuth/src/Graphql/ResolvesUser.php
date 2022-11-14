<?php

namespace GPDAuth\Graphql;

class ResolvesUser
{


    public static function getFullNameResolve(): callable
    {

        return function ($root, $args, $context, $info) {
            $firstName = $root["firstName"] ?? '';
            $lastName = $root["lastName"] ?? '';
            $fullName = $firstName . " " . $lastName;
            return trim($fullName);
        };
    }
}
