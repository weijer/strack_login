<?php

namespace StrackOauth\Provider;

use Adldap\Adldap;


class Ldap
{

    // adldap 域操作对象
    protected $adldap;

    // 供应商列表
    protected $provider = [];

    // 错误信息
    protected $errorMessage = "";

    /**
     * Ldap constructor.
     * @param $param
     */
    public function __construct($param)
    {
        $this->adldap = new Adldap();
    }

    /**
     * 添加域供应商
     * @param $config
     * @param string $name
     */
    protected function addProvider($config, $name = 'default')
    {
        if (!in_array($name, $this->provider)) {
            $this->provider[$name] = $this->adldap->addProvider($config, $name);
        }
    }

    /**
     * 发起连接
     * @param string $name
     * @return bool
     */
    protected function connect($name = 'default')
    {
        try {
            $this->adldap->connect($name);
            return true;
        } catch (\Adldap\Auth\BindException $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
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
     * @param $config
     * @return bool
     */
    public function testConfig($config)
    {
        $this->addProvider($config);
        return $this->connect();
    }

    /**
     * 获得基础DN
     * @param $config
     * @param string $name
     * @return bool
     */
    public function getBaseDn($config, $name = 'default')
    {
        $this->addProvider($config, $name);
        if($this->connect($name)){
            $baseDn = $this->provider[$name]->search()->findBaseDN();
            return $baseDn;
        }else{
            return false;
        }
    }

    /**
     * 验证LDAP登录用户
     * @param $config
     * @param $param
     * @param string $name
     * @return bool
     */
    public function verify($config, $param, $name = 'default')
    {
        $this->addProvider($config, $name);
        if($this->connect($name)){
            $param["login_name"] = $this->getDcName($config, $name) . "\\" . $param["login_name"];
            if ($this->provider[$name]->auth()->attempt($param['login_name'], $param['password'])) {
                return true;
            } else {
                $this->errorMessage = "Ldap user not exist.";
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 获得BaseDN下的首个Dc
     * @param $config
     * @param string $name
     * @return mixed
     */
    public function getDcName($config, $name = 'default')
    {
        $root = $this->getBaseDn($config, $name);
        $DC = explode(",", $root)["0"];
        $adName = explode("=", $DC)["1"];
        return $adName;
    }

    /**
     * 获取用户的信息
     * @param $config
     * @param $param
     * @param string $name
     * @return bool|mixed
     */
    public function getUserData($config, $param, $name = 'default')
    {
        $this->addProvider($config, $name);
        if($this->connect($name)){
            $search = $this->provider[$name]->search();
            $resData = $search->findBy('samaccountname', $param["login_name"]);
            return $resData;
        }else{
            return false;
        }
    }

    /**
     * 获取DN下的成员
     * @param $config
     * @param string $name
     * @return bool
     */
    public function getDnMember($config, $name = 'default')
    {
        $this->addProvider($config, $name);
        if($this->connect($name)){
            //管理员身份绑定登录
            $this->provider[$name]->auth()->bindAsAdministrator();
            $search = $this->provider[$name]->search();
            //all用户获取
            $results = $search->all();
            return $results;
        }else{
            return false;
        }
    }
}
