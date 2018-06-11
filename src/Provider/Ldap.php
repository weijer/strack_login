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

    public function verify($login,$password,$config)
    {
        
        $this->adldap->addProvider($config);

        try {
            $provider = $this->adldap->connect();
            $search   = $provider->search();
            if ($provider->auth()->attempt($login,$password)) {
                return true;
            }else{
                return false;
            }
        } catch (\Adldap\Models\BindException $e) {
            die('连接失败');
        }
    }

    public function getUserData($config)
    {
//        $config = ['domain_controllers' => ['192.168.31.126'],
//                   'base_dn'            => 'CN=Administrator,CN=Users,DC=sayms,DC=com',
//                   'admin_username'     => 'CN=Administrator,CN=Users,DC=sayms,DC=com',
//                   'admin_password'     => 'P@ssw0rd',];
        $ldapName = 'CN=Administrator,CN=Users,DC=sayms,DC=com';
        $password = 'P@ssw0rd';
        $username = 'Administrator';
        $this->adldap->addProvider($config);
        try {
            $provider = $this->adldap->connect();
            $search   = $provider->search();

            if ($provider->auth()->attempt($ldapName, $password)) {
                $record = $search->findBy('samaccountname', $username);
                $status = 200;
//                $message = lang("create_token_success");
                $userinfo = [
                        'company'   =>  $record['company'][0],
                        'title'     =>  $record['title'][0],
                        'telephonenumber' => $record['telephonenumber'][0],
                        'mail'      =>  $record['mail'][0],
                        'sn'        =>  $record['sn'][0],
                        'givenname' =>  $record['givenname'][0],
                        'userprincipalname' =>  $record['userprincipalname'][0],
                            ];

                return $userinfo;

            } else {

                $status = 404;
//                $data = [];
//                $message = lang("ldap_auth_error");
            }
//            return ["status" => $status, "data" => $data, 'message' => $message];
        } catch (\Adldap\Auth\BindException $e) {
            echo '连接失败';
        }
    }
    public function getAllUserData($config)

        {

//            $config = ['domain_controllers' => ['192.168.31.126'],
//                       'base_dn'            => 'CN=Users,DC=sayms,DC=com',
//                       'admin_username'     => 'CN=Administrator,CN=Users,DC=sayms,DC=com',
//                       'admin_password'     => 'P@ssw0rd',];


            $this->adldap->addProvider($config);
            try {
                $ldapName = 'uid=riemann,dc=example,dc=com';
                $password = 'password';
                $provider = $this->adldap->connect();
//                $provider->auth()->bindAsAdministrator();
                $search   = $provider->search();
                $record = $search->findByDn('cn=read-only-admin,dc=example,dc=com');
                $results = $provider->search()->whereHas('cn')->get();

//                $record =  $search->select(['cn', 'samaccountname', 'telephone', 'mail']);
//                $results = $search->all();
//                $userinfo = $results;

//                    [
//                    'company'   =>  $record['company'][0],
//                    'title'     =>  $record['title'][0],
//                    'telephonenumber' => $record['telephonenumber'][0],
//                    'mail'      =>  $record['mail'][0],
//                    'sn'        =>  $record['sn'][0],
//                    'givenname' =>  $record['givenname'][0],
//                    'userprincipalname' =>  $record['userprincipalname'][0],
//                ];
                echo '<pre>';
//                var_dump($results['20']['mail']);

                var_dump($results);
//                return $results;
            } catch (\Adldap\Auth\BindException $e) {
               echo '连接失败';
            }
        }
}
