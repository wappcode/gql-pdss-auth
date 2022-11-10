<?php

namespace GPDAuth\Library;

class PasswordManager
{

    const SHA256 = 'sha256';
    const SHA1 = 'sha1';
    const MD5 = 'md5';


    public static function encode(string $password, ?string $salt = null, ?string $hashAlgorithm = null): string
    {
        $algo = $hashAlgorithm ?? static::SHA256;
        if ($salt) $password = $password . $salt;
        return hash($algo, $password);
    }
    public static function createSalt(string $hashAlgorithm = 'sha256')
    {
        $randomkey = uniqid('salt', true);
        return hash($hashAlgorithm, $randomkey);
    }
}
