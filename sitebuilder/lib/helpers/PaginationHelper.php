<?php

class PaginationHelper extends Helper {
    protected $model;

    public function model($model) {
        $this->model = Model::load($model);
        return $this;
    }
    
    public function numbers($options = array()) {
        if(!$this->model) {
            return null;
        }

        $options += array(
            'modulus' => 3,
            'separator' => ' ',
            'tag' => 'span',
            'current' => 'current'
        );
        $page = $this->page();
        $pages = $this->pages();
        $numbers = array();
        $start = max($page - $options['modulus'], 1);
        $end = min($page + $options['modulus'], $pages);

        for($i = $start; $i <= $end; $i++) {
            if($i == $page) {
                $attributes = array('class' => $options['current']);
                $number = $i;
            }
            else {
                $attributes = array();
                $number = $this->html->link($i, array('page' => $i));
            }
            $numbers []= $this->html->tag($options['tag'], $number, $attributes);
        }

        return join($options['separator'], $numbers);
    }
    
    public function next($text, $attr = array()) {
        if($this->hasNext()) {
            return $this->html->link($text, array(
                'page' => $this->page() + 1
            ), $attr);
        }
    }
    
    public function previous($text, $attr = array()) {
        if($this->hasPrevious()) {
            return $this->html->link($text, array(
                'page' => $this->page() - 1
            ), $attr);
        }
    }
    
    public function first($text, $attr = array()) {
        if($this->hasPrevious()) {
            return $this->html->link($text, array(
                'page' => 1
            ), $attr);
        }
    }
    
    public function last($text, $attr = array()) {
        if($this->hasNext()) {
            return $this->html->link($text, array(
                'page' => $this->model->pagination['totalPages']
            ), $attr);
        }
    }
    
    public function hasNext() {
        if($this->model) {
            return $this->page() < $this->pages();
        }
    }

    public function hasPrevious() {
        if($this->model) {
            return $this->page() > 1;
        }
    }

    public function page() {
        if($this->model) {
            return $this->model->pagination['page'];
        }
    }
    
    public function pages() {
        if($this->model) {
            return $this->model->pagination['totalPages'];
        }
    }
    
    public function records() {
        if($this->model) {
            return $this->model->pagination['totalRecords'];
        }
    }
}