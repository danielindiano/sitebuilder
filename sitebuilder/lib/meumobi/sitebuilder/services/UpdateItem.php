<?php

namespace meumobi\sitebuilder\services;

use Inflector;
use Model;
use meumobi\sitebuilder\Logger;
use meumobi\sitebuilder\WorkerManager;
use meumobi\sitebuilder\validators\ItemsPersistenceValidator;
use meumobi\sitebuilder\validators\ParamsValidator;

class UpdateItem
{
	public function update($item, $data = null, $options = [])
	{
		if ($data) {
			$blacklist = ['medias'];
			$keys = array_keys($data);
			$allowedKeys = array_diff($keys, $blacklist);

			foreach ($allowedKeys as $k) {
				$item->set([ $k => $data[$k] ]);
			}

			$media = [];

			if (isset($data['medias'])) {
				foreach ($data['medias'] as $medium) {
					$finder = function ($i) use ($medium) { return $i->url == $medium['url']; };
					if ($m = $item->medias->find($finder)->first()) {
						$media []= $m->to('array') + $medium;
					} else {
						$media []= $medium;
					}
				}

				unset($item['medias']);
				$item->set([ 'medias' => $media ]);
			}
		}

		$validator = new ItemsPersistenceValidator();
		$validationResult = $validator->validate($item);

		if ($validationResult->isValid()) {
			$item->save();

			Logger::info('items', 'item updated', [
				'item_id' => $item->id(),
				'site_id' => $item->site_id,
				'category_id' => $item->parent_id,
			]);

			$this->addMediaFileSize($item);
			$this->createMediaThumbnails($item);

			$updated = true;
		} else {
			Logger::info('items', 'item cannot be updated', [
				'id' => $item->id(),
				'site_id' => $item->site_id,
				'category_id' => $item->parent_id,
				'errors' => $validationResult->errors(),
			]);

			$updated = false;
		}

		return [$updated, $validationResult->errors()];
	}

	protected function addMediaFileSize($item)
	{
		$hasMedias = $item->medias && count($item->medias->to('array'));

		if ($hasMedias) {
			WorkerManager::enqueue('media_filesize', ['item_id' => $item->id()]);
		} else {
			Logger::debug('items', 'not creating media_filesize job', [
				'item_id' => $item->id(),
				'site_id' => $item->site_id,
				'reason' => 'item has no media',
			]);
		}
	}

	protected function createMediaThumbnails($item)
	{
		$hasMedias = count($item->medias->to('array'));

		if ($hasMedias) {
			$job = WorkerManager::enqueue('media_thumbnailer', ['item_id' => $item->id()]);
		} else {
			Logger::debug('items', 'not creating media_thumbnailer job', [
				'reason' => 'item has no media',
			]);
		}
	}

}