<?php

class Hookable {
    protected $actions = array();
    protected $filters = array();

    public function register($type, $hook, $method) {
        if(!array_key_exists($hook, $this->{$type})):
            $this->{$type}[$hook] = array();
        endif;
        $this->{$type}[$hook] []= $method;
    }
    public function fireAction($hook, $params = array()) {
        $hook = $this->getHook($hook, 'actions');
        foreach($hook as $method):
            $this->callHook($method, $params);
        endforeach;
    }
    public function fireFilter($hook, $param) {
        $hook = $this->getHook($hook, 'filters');
        foreach($hook as $method):
            $param = $this->callHook($method, array($param));
            if(!$param):
                break;
            endif;
        endforeach;

        return $param;
    }
    protected function getHook($name, $type) {
        if(is_string($name)):
            $hooks = array();
        
            if(array_key_exists($name, $this->{$type})):
                $hooks = $this->{$type}[$name];
            endif;
            
            if(property_exists($this, $name)):
                $hooks = array_merge($hooks, $this->{$name});
            endif;

            return $hooks;
        else:
            return $name;
        endif;
    }
    protected function callHook($method, $params) {
        if(!is_callable($method)):
            $method = array($this, $method);
        endif;
        
        return call_user_func_array($method, $params);
    }
}