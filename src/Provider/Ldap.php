<?php

namespace Provider;

use Adldap\Adldap;

class Ldap
{

    protected $adldap;

    /**
     * Ldap constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->adldap = new Adldap();
        $this->adldap->addProvider($config);
    }

    public function verify()
    {
        try {
            // If a successful connection is made to your server, the provider will be returned.
            $provider = $this->adldap->connect();

            // Performing a query.
            $results = $provider->search()->where('cn', '=', 'John Doe')->get();

            // Finding a record.
            $user = $provider->search()->find('jdoe');

            // Creating a new LDAP entry. You can pass in attributes into the make methods.
            $user = $provider->make()->user([
                'cn' => 'John Doe',
                'title' => 'Accountant',
                'description' => 'User Account',
            ]);

            // Setting a model's attribute.
            $user->cn = 'John Doe';

            // Saving the changes to your LDAP server.
            if ($user->save()) {
                // User was saved!
            }
        } catch (\Adldap\Auth\BindException $e) {
            // There was an issue binding / connecting to the server.

        }
    }

    public function getUserData()
    {
        // TODO: Implement getUserData() method.
    }

    public function getAllUserData()
    {
        // TODO: Implement getAllUserData() method.
    }
}