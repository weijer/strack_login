<?php

namespace StrackOauth\Provider;

use Adldap\Adldap;
use Adldap\Schemas\ActiveDirectory;


class Ldap
{

    protected $adldap;
    /**
     * Ldap constructor.
     * @param $config
     */
    public function __construct()
    {
        $this->adldap = new Adldap();
    }

    /**
     * @param $config
     */
    public function addProvider($config)
    {
        $this->adldap->addProvider($config);

    }

    /**
     * 获得AD名字
     * @param $config
     * @return mixed
     */
    public function getAdName($config)
    {
        $this->adldap->addProvider($config);
        $provider = $this->adldap->connect();
        $root = $provider->search()->getRootDse()->getRootDomainNamingContext();
        $DC=explode(",",$root)["0"];
        $adName=explode("=",$DC)["1"];
        return $adName;
    }

    /**
     * 验证LDAP参数
     * @param $param
     * @param $config
     * @return bool
     * @throws \Adldap\Auth\BindException
     * @throws \Adldap\Auth\PasswordRequiredException
     * @throws \Adldap\Auth\UsernameRequiredException
     */
    public function verify($param, $config)
    {
        $this->adldap->addProvider($config);
        try {
            $provider = $this->adldap->connect();
            $param["login_name"]=$this->getAdName($config)."\\".$param["login_name"];
            if ($provider->auth()->attempt($param['login_name'], $param['password'])) {
                return true;
            } else {
                return false;
            }
        } catch (\Adldap\Models\BindException $e) {
            echo "Credentials were incorrect";
        }
    }

    /**
     * 获取单个用户的信息
     * @param $param
     * @param $config
     * @return mixed
     */
    public function ldapData($param, $config)
    {
        $this->adldap->addProvider($config);
        try {
            $provider = $this->adldap->connect();
            $search   = $provider->search();
            //获得根域名
            $root = $search->getRootDse()->getRootDomainNamingContext();
            $resData  = $search->findBy('samaccountname', $param["login_name"]);
            return $resData;
        } catch (\Adldap\Auth\BindException $e) {
            echo 'Credentials were incorrect';
        }
    }

    /**
     * 获取组中所有信息
     * @param $param
     * @param $config
     * @return array|\Illuminate\Support\Collection
     */
    public function ldapAllData($param, $config)
    {
        //重新拼装配置参数
        $config["base_dn"] = $param;
        $this->adldap->addProvider($config);
        try {
            $provider = $this->adldap->connect();
            //管理员身份绑定登录
            $provider->auth()->bindAsAdministrator();
            $search = $provider->search();
            //all用户获取
            $results = $search->all();
            return $results;
        } catch (\Adldap\Auth\BindException $e) {
            echo 'Credentials were incorrect';
        }
    }
}
