<?php

namespace app\controllers\api;

use Exception;
use I18n;
use Inflector;
use Model;
use View;
use app\models\Items;
use lithium\core\Object;
use meumobi\sitebuilder\presenters\api\RssItemPresenter;
use meumobi\sitebuilder\repositories\RecordNotFoundException;
use meumobi\sitebuilder\services\CreateItem;
use meumobi\sitebuilder\services\UpdateItem;

class ItemsController extends ApiController {
	const PAGE_LIMIT = 20;

	protected $skipBeforeFilter = ['requireVisitorAuth'];

	public function index()
	{
		$this->requireVisitorAuth();

		$category_id = $this->request->get('params:category_id');
		$category = Model::load('Categories')->firstById($category_id);
		list($orderField, $orderDirection) = explode(',', $this->param('order', 'order,DESC'));
		$params = [
			'order' => [$orderField => $orderDirection],
			'conditions' => [
				'site_id' => $this->site()->id,
				'parent_id' => $category->id,
				'type' => $category->type,
				'is_published' => true,
			],
			'limit' => $this->param('limit', self::PAGE_LIMIT),
			'page' => $this->param('page', 1)
		];

		$url = "/api/{$this->site()->domain()}/categories/{$category->id}/items";
		$url_params = $this->request->query;;

		return $this->paginate($params, $url, $url_params);
	}

	public function index_rss()
	{
		$category_id = $this->request->get('params:category_id');
		$category = Model::load('Categories')->firstById($category_id);
		$params = [
			'order' => ['created' => 'desc'],
			'conditions' => [
				'site_id' => $this->site()->id,
				'parent_id' => $category->id,
				'type' => $category->type,
				'is_published' => true,
			],
			'limit' => $this->param('limit', self::PAGE_LIMIT),
		];

		$url = "/api/{$this->site()->domain()}/categories/{$category->id}/items";
		$url_params = ['category' => $category_id];

		$items = $this->getItems($params, $url, $url_params);

		$data = [
			'items' => RssItemPresenter::presentSet($items),
			'site' => $this->site()
		];

		$view = new View();
		return $view->render('items/feed.rss', $data, false);
	}

	public function promotions()
	{
		$this->requireVisitorAuth();

		$category_id = $this->request->get('params:category_id');
		$date = $this->request->get('query:time') ?: time();

		$category = Model::load('Categories')->firstByIdAndType($category_id, 'promotions');

		if (!$category) {
			throw new RecordNotFoundException('category does not have promotions');
		}

		$params = [
			'order' => ['order' => 'ASC'],
			'conditions' => [
				'site_id' => $this->site()->id,
				'parent_id' => $category->id,
				'type' => $category->type,
				'start' => ['$lt' => $date],
				'end' => ['$gt' => $date]
			],
			'limit' => $this->param('limit', self::PAGE_LIMIT),
			'page' => $this->param('page', 1)
		];

		$url = "/api/{$this->site()->domain()}/categories/{$category->id}/promotions";
		$url_params = ['time' => $date];

		return $this->paginate($params, $url, $url_params, null, '\app\models\items\Promotions');
	}

	public function related()
	{
		$this->requireVisitorAuth();

		$item = Items::find('first', [
			'conditions' => [
				'_id' => $this->request->get('params:id'),
				'site_id' => $this->site()->id
			]
		]);

		if (!$item) {
			throw new RecordNotFoundException('item not found');
		}

		if (!$item->related) {
			return ['items' => array()];
		}

		$params = [
			'conditions' => [
				'_id' => $item->related->to('array'),
				'site_id' => $this->site()->id
			],
			'limit' => $this->param('limit', self::PAGE_LIMIT),
			'page' => $this->param('page', 1)
		];

		$url = "/api/{$this->site()->domain()}/items/{$item->id()}/related";
		$url_params = [];

		return $this->paginate($params, $url, $url_params);
	}

	public function search()
	{
		$this->requireVisitorAuth();

		$conditions = $this->postConditions($this->request->query, [
			'title' => 'like',
			'description' => 'like'
		]);
		$conditions['is_published'] = true;
		$conditions['site_id'] = $this->site()->id;

		$params = [
			'conditions' => $conditions,
			'limit' => $this->param('limit', self::PAGE_LIMIT),
			'page' => $this->param('page', 1)
		];

		$url = "/api/{$this->site()->domain()}/items/search";
		$url_params = $this->request->query;

		return $this->paginate($params, $url, $url_params, function($items, $item) {
			$classname = '\app\models\items\\' . Inflector::camelize($item['type']);
			$items[$item['type']] []= $classname::create($item)->toJSON($this->visitor());
			return $items;
		});
	}

	public function show()
	{
		$this->requireVisitorAuth();

		$item = Items::find('type', array('conditions' => array(
			'_id' => $this->request->params['id'],
			'site_id' => $this->site()->id
		)));

		return $item->toJSON($this->visitor());
	}

	public function latest()
	{
		$this->requireVisitorAuth();

		$parent_id = $this->request->get('params:parent_id');

		$params = [
			'conditions' => [
				'site_id' => $this->site()->id,
				'is_published' => true,
			],
			'order' => ['published' => 'DESC'],
			'limit' => $this->param('limit', self::PAGE_LIMIT),
			'page' => $this->param('page', 1),
		];

		if ($parent_id) {
			$params['conditions']['parent_id'] = $parent_id;
		} else {
			$categories = Model::load('Categories')->allBySiteIdAndVisibilityAndLatestFeedEligible($this->site()->id, 1,1);
			$category_ids = array_map(function($category) {
				return $category->id;
			}, $categories);

			$params['conditions']['parent_id'] = [ '$in' => $category_ids ];
		}

		$url = "/api/{$this->site()->domain()}/items/latest";
		$url_params = ['parent_id' => $parent_id];

		return $this->paginate($params, $url, $url_params);
	}

	public function by_category()
	{
		$this->requireVisitorAuth();

		$categories = Model::load('Categories')->allBySiteIdAndVisibility($this->site()->id, 1);
		$items = array();

		foreach($categories as $category) {
			$current_items = $category->childrenItems($this->param('limit', 20));
			$items[$category->id] = $current_items->to('array');
		}

		return $items;
	}

	public function create()
	{
		$this->requireVisitorAuth();

		$data = $this->request->data;
		$data['site_id'] = $this->site()->id;

		$itemCreationService = new CreateItem();
		$item = $itemCreationService->build($data);

		list($created, $errors) = $itemCreationService->create($item, [
			'sendPush' => true
		]);

		if ($created) {
			$images = $this->request->get('data:images');
			if ($images) {
				foreach ($images as $id => $image) {
					if (!is_numeric($id)) continue;
					$record = Model::load('Images')->firstById($id);
					$record->title = $image['title'];
					$record->foreign_key = $item->id();
					$record->save();
				}
			}

			$this->response->status(201);
			return $item->toJSON($this->visitor());
		} else {
			$errors = array_map(function($error) {
				return I18n::translate($error);
			}, array_values($errors));

			$this->response->status(422);
			return [
				'error' => 'could not save item',
				'errors' => $errors
			];
		}
	}

	public function update()
	{
		$this->requireVisitorAuth();

		$item = Items::find('first', array('conditions' => array(
			'_id' => $this->request->params['id'],
			'site_id' => $this->site()->id
		)));

		if (!$item) throw new RecordNotFoundException('item not found');

		$item->set(array(
			'site_id' => $this->site()->id
		) + $this->request->data);

		if ($item->save()) {
			$this->response->status(200);
			return $item->toJSON($this->visitor());
		} else {
			$this->response->status(422);
		}
	}

	public function destroy()
	{
		$this->requireVisitorAuth();

		Items::remove(array('_id' => $this->request->params['id']));
		$this->response->status(200);
	}

	public function news()
	{
		$this->requireVisitorAuth();

		if (!$category = $this->site()->newsCategory())
			return ['items' => []];

		$params = [
			'order' => ['published' => 'DESC'],
			'conditions' => [
				'site_id' => $this->site()->id,
				'parent_id' => $category->id
			],
			'limit' => $this->param('limit', self::PAGE_LIMIT),
			'page' => $this->param('page', 1)
		];

		$url = "/api/{$this->site()->domain()}/news";
		$url_params = [];

		return $this->paginate($params, $url, $url_params);
	}

	protected function prepareAdd($data)
	{
		$need = array('type', 'parent_id');
		$discard = array('created', 'updated', 'geo');

		foreach ($discard as $field) {
			if (array_key_exists($field, $data)) unset($data[$field]);
		}

		foreach ($need as $field) {
			if (!array_key_exists($field, $data)) {
				throw new Exception('need more params: '. $field);
			}
		}

		return $data;
	}

	protected function checkEtag()
	{
		if ($this->request->params['action'] != 'promotions') {
			parent::checkEtag();
		}
	}

	protected function getItems($params, $url, $url_params, $reduce = null, $itemsClass = '\app\models\Items') {
		if ($this->site()->private) {
			$groups = $this->visitor() ? $this->visitor()->groups() : [];
			$params['conditions']['groups'] = array_merge($groups, [[]]);
		}

		$items = $itemsClass::find('all', $params)->to('array');

		if ($reduce) {
			return array_reduce($items, $reduce, []);
		} else {
			return array_map(function($item) {
				$classname = '\app\models\items\\' .
					Inflector::camelize($item['type']);
				return $classname::create($item)->toJSON($this->visitor());
			}, $items);
		}
	}

	protected function paginate($params, $url, $url_params, $reduce = null, $itemsClass = '\app\models\Items')
	{
		$items = $this->getItems($params, $url, $url_params, $reduce, $itemsClass);
		$response = ['items' => $items];

		$count = Items::find('count', ['conditions' => $params['conditions']]);
		$pages = ceil($count / $params['limit']);
		$meta = [];
		$url_params['limit'] = $params['limit'];

		if ($params['page'] > 1) {
			$meta['previous'] = $this->parseUrl($url, [
				'page' => $params['page'] - 1
				] + $url_params
			);
		}

		if ($pages > $params['page']) {
			$meta['next'] = $this->parseUrl($url, [
				'page' => $params['page'] + 1
				] + $url_params
			);
		}

		if (!empty($meta)) $response['_meta'] = $meta;

		return $response;
	}

	protected function parseUrl($url, $params)
	{
		$query = http_build_query($params);
		return $url . '?' . $query;
	}
}
