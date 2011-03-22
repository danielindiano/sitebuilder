<?php

require 'lib/SimplePie.php';

class Feeds extends AppModel {
    protected $beforeDelete = array('deleteArticles');

    public function updateArticles() {
        $articles = Model::load('Articles');
        $feed = $this->getFeed();
        $items = $feed->get_items();
        foreach($items as $item) {
            if(!$articles->articleExists($this->id, $item->get_id())) {
                $articles->addToFeed($this, $item);
            }
        }

        $this->save(array(
            'updated' => date('Y-m-d H:i:s')
        ));
    }

    public function saveFeed($link) {
        $feed = $this->firstByLink($link);
        if(is_null($feed)) {
            $this->save(array(
                'link' => $link
            ));
            $feed = $this->firstById($this->id);
        }
        $feed->updateArticles();

        return $feed;
    }

    public function topArticles() {
        return Model::load('Articles')->topByFeedId($this->id);
    }

    protected function getFeed() {
        $feed = new SimplePie();
        $feed->set_cache_location(FileSystem::path('tmp/cache/simplepie'));
        $feed->set_feed_url($this->link);
        $feed->init();

        return $feed;
    }

    protected function deleteArticles($id) {
        $model = Model::load('Articles');
        $items = $model->allByFeedId($id);
        $this->deleteSet($model, $items);

        return $id;
    }
}