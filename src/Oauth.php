<?php

namespace StrackOauth;

use Provider\Ldap;
use Provider\QQ;
use Provider\Wechat;

class Oauth
{

    const ALLOW_PROVIDER = ['Ldap', 'QQ', 'Wechat'];

    protected $provider = null;

    /**
     *
     * Oauth constructor.
     * @param $param
     */
    public function __construct($param)
    {
        if (in_array($param["provider"], self::ALLOW_PROVIDER)) {
            $this->provider = new $param["provider"]($param);
        }
        return $this->provider;
    }
}