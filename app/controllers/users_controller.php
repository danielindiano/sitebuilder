<?php

class UsersController extends AppController {
    protected $redirectIf = array('register', 'login');
    
    protected function beforeFilter() {
        if(Auth::loggedIn()) {
            foreach($this->redirectIf as $rule) {
                if($rule == $this->param('action')) {
                    $this->redirect('/categories');
                }
            }
        }
        
        parent::beforeFilter();
    }
    
    public function edit() {
        $user = Auth::user();
        $this->saveUser($user, '/users/edit');
    }
    
    public function register() {
        $user = new Users($this->data);
        $this->saveUser($user, '/sites/register');
    }
    
    public function login() {
        if(!empty($this->data)) {
            $user = Auth::identify($this->data);
            if($user) {
                Auth::login($user);
                $this->redirect('/categories');
            }
        }
    }
    
    public function logout() {
        Auth::logout();
        $this->redirect('/');
    }
    
    protected function saveUser($user, $redirect) {
        if(!empty($this->data)) {
            $user->updateAttributes($this->data);
            if($user->validate()) {
                $user->save();
                Session::writeFlash("success", __("Configurações salvas com sucesso."));
                $this->redirect($redirect);
            }
        }
        $this->set(array(
            'user' => $user
        ));
    }
}