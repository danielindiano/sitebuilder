<?php

namespace app\controllers\api;

use Model;
use Inflector;
use app\models\Items;

class GeoController extends ApiController {
    public function nearest() {
        $category = Model::load('Categories')->firstById($this->request->params['category_id']);

        $classname = '\app\models\items\\' . Inflector::camelize($category->type);
        $items = $classname::find('nearest', array('conditions' => array(
            'site_id' => $this->site()->id,
            'parent_id' => $category->id,
            'lat' => $this->request->query['lat'],
            'lng' => $this->request->query['lng']
        )));

        $type = $category->type;
        $etag = $this->etag($items);
        $self = $this;

        return $this->whenStale($etag, function() use($type, $items, $self) {
            return array($type => $items);
        });
    }

    public function inside() {
        $category = Model::load('Categories')->firstById($this->request->params['category_id']);

        $classname = '\app\models\items\\' . Inflector::camelize($category->type);
        $items = $classname::find('within', array('conditions' => array(
            'site_id' => $this->site()->id,
            'parent_id' => $category->id,
            'ne_lat' => $this->request->query['ne_lat'],
            'ne_lng' => $this->request->query['ne_lng'],
            'sw_lat' => $this->request->query['sw_lat'],
            'sw_lng' => $this->request->query['sw_lng']
        )));

        $type = $category->type;
        $etag = $this->etag($items);
        $self = $this;

        return $this->whenStale($etag, function() use($type, $items, $self) {
            return array($type => $items);
        });
    }
}
