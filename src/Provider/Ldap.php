<?php

namespace StrackOauth\Provider;

use Adldap\Adldap;

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

    public function addProvider($config)
    {
        $this->adldap->addProvider($config);

    }

    public function verify($param, $config)
    {

        $this->adldap->addProvider($config);
        try {
            $provider = $this->adldap->connect();
            $search   = $provider->search();


            if ($provider->auth()->attempt($param['login_name'], $param['password'])) {
                return true;
            } else {
                return false;
            }
        } catch (\Adldap\Models\BindException $e) {
           echo "Credentials were incorrect";
        }
    }

    public function ldapUserData($param, $config)
    {

        $this->adldap->addProvider($config);

        try {
            $provider = $this->adldap->connect();
            $search   = $provider->search();
            $username = explode("\\",$param['login_name']);
            $record   = $search->findBy('samaccountname', $username[1]);
            //uid查找方式
//            $record = $provider->search()->where("uid", '=', '')->get();
            $data = [
                'title'=>$record['title'][0],
                'department'=>$record['department'][0],
                'mail'=>$record['mail'][0],
                'givenname'=> $record['givenname'][0],
                'sn'=>$record['sn'][0],
                'cn'=>$record['cn'][0],
                'description'=>$record['description'][0],
                'telephonenumber'=>$record['telephonenumber'][0],
                'company'=>$record['company'][0],
            ];
            return $data;
        } catch (\Adldap\Auth\BindException $e) {
            echo 'Credentials were incorrect';
        }
    }

    public function ldapAllUserData($param, $config)

    {


        $this->adldap->addProvider($param, $config);
        try {
            $provider = $this->adldap->connect();
//            管理员身份绑定登录
//          $provider->auth()->bindAsAdministrator();
            $search  = $provider->search();
//            DN查找
//            $record  = $search->findByDn('cn=read-only-admin,dc=example,dc=com');
//            CN查找
            $results = $provider->search()->whereHas('cn')->get();

            //all用户获取
//          $results = $search->all();
//          $userinfo = $results;

        } catch (\Adldap\Auth\BindException $e) {
            echo 'Credentials were incorrect';
        }
    }
}
