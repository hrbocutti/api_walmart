<?php

namespace App\Factory;

/**
 * Created by PhpStorm.
 * User: Dev01
 * Date: 02/08/2017
 * Time: 09:44
 */
class DbFactory
{
    protected $resolver = null;

    function __construct()
    {
        if ($this->resolver != null){
            $this->resolver = null;
        }
    }

    /**
     * Factory de Conexao Local
     */
    public function factoryLocal()
    {
        // Database information
        $settings = array(
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'db_frete',
            'username' => 'root',
            'password' => '',
            'collation' => 'utf8_general_ci',
            'prefix' => ''
        );

        // Bootstrap Eloquent ORM
        $container = new \Illuminate\Container\Container;
        $connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
        $conn = $connFactory->make($settings);
        $this->resolver = new \Illuminate\Database\ConnectionResolver();
        $this->resolver->addConnection('default', $conn);
        $this->resolver->setDefaultConnection('default');
        \Illuminate\Database\Eloquent\Model::setConnectionResolver($this->resolver);
    }

    /**
     * Factory de Conexao instanciavel
     * @param $host
     * @param $data_base
     * @param $usr_name
     * @param $password
     */
    public function factoryConnection($host, $data_base, $usr_name, $password)
    {
        // Database information
        $settings = array(
            'driver' => 'mysql',
            'host' => "$host",
            'database' => "$data_base",
            'username' => "$usr_name",
            'password' => "$password",
            'collation' => 'utf8_general_ci',
            'prefix' => ''
        );

        // Bootstrap Eloquent ORM
        $container = new \Illuminate\Container\Container;
        $connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
        $conn = $connFactory->make($settings);
        $this->resolver = new \Illuminate\Database\ConnectionResolver();
        $this->resolver->addConnection('default', $conn);
        $this->resolver->setDefaultConnection('default');
        \Illuminate\Database\Eloquent\Model::setConnectionResolver($this->resolver);
    }
}