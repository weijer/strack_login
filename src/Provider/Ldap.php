<?php

namespace StrackOauth\Provider;

use Adldap\Adldap;


class Ldap
{

    protected $adldap;
    protected $errorMessage = "";

    /**
     * Ldap constructor.
     * @param $config 配置参数
     */
    public function __construct($config)
    {
        $this->adldap = new Adldap();
        $this->adldap->addProvider($config);

    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->errorMessage;
    }

    /**
     * 测试配置
     * @return bool
     */
    public function testConfig()
    {
        try {
            $this->adldap->connect();
            return true;
        } catch (\Adldap\Auth\BindException $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * 获得基础DN
     * @return mixed
     */
    public function getBaseDn()
    {
        $provider = $this->adldap->connect();
        $baseDn   = $provider->search()->findBaseDN();
        return $baseDn;
    }

    /**
     * 验证LDAP用户
     * @param $param
     * @return bool
     */
    public function verify($param)
    {
        try {
            $provider            = $this->adldap->connect();
            $param["login_name"] = $this->getDcName() . "\\" . $param["login_name"];
            if ($provider->auth()->attempt($param['login_name'], $param['password'])) {
                return true;
            } else {
                $this->errorMessage = L("LDAP_User_Not_Exist");
                return false;
            }
        } catch (\Adldap\Models\BindException $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * 获得BaseDN下的首个Dc
     * @return mixed
     */
    public function getDcName()
    {
        $root     = $this->getBaseDn();
        $DC       = explode(",", $root)["0"];
        $adName   = explode("=", $DC)["1"];
        return $adName;
    }

    /**
     * 获取用户的信息
     * @param $param
     * @return mixed
     */
    public function getUserData($param)
    {
        try {
            $provider = $this->adldap->connect();
            $search   = $provider->search();
            $resData  = $search->findBy('samaccountname', $param["login_name"]);
            return $resData;
        } catch (\Adldap\Auth\BindException $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取DN下的成员
     * @return bool
     */
    public function getDnMember()
    {
        try {
            $provider = $this->adldap->connect();
            //管理员身份绑定登录
            $provider->auth()->bindAsAdministrator();
            $search = $provider->search();
            //all用户获取
            $results = $search->all();
            return $results;
        } catch (\Adldap\Auth\BindException $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }
}
