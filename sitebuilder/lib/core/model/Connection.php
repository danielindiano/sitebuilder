<?php

require 'lib/core/model/datasources/Datasource.php';

class Connection {
    protected $config = array();
    protected $connections = array();
    protected static $instance;

    public static function instance() {
        if(!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        
        return self::$instance;
    }
    
    public static function add($name, $connection = null) {
        $self = self::instance();

        if(is_array($name)) {
            $self->config += $name;
        }
        else {
            $self->config[$name] = $connection;
        }
    }
    
    public static function config($name) {
        return self::instance()->config[$name];
    }
    
    public static function get($connection) {
        $self = self::instance();
        
        if(!array_key_exists($connection, $self->config)) {
            throw new RuntimeException('Can\'t find "' . $connection . '" database configuration.');
        }

        $config = $self->config[$connection];
        if(!array_key_exists($connection, $self->connections)) {
            $self->connections[$connection] = self::create($config);
        }
        
        return $self->connections[$connection];
    }
    
    public static function create($config) {
        $datasource = $config['driver'] . 'Datasource';
        if(!class_exists($datasource)) {
            require 'lib/core/model/datasources/' . $datasource . '.php';
        }
        
        return new $datasource($config);
    }
}
