<?php

namespace StrackOauth;

class Oauth
{

    const ALLOW_PROVIDER = ['Ldap', 'QQ', 'Wechat'];

    public static $provider;

    /**
     *
     * Oauth constructor.
     * @param $param
     */
    public function __construct($param)
    {
        if (in_array($param["provider"], self::ALLOW_PROVIDER)) {
            $class = '\\StrackOauth\\Provider\\' . $param["provider"];
            self::$provider = new $class($param);
        }
    }
}