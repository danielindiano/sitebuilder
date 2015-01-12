<?php

namespace meumobi\sitebuilder\workers;

use app\models\Items;
use meumobi\sitebuilder\repositories\RecordNotFoundException;//TODO move exceptions for a more generic namespace
use pushwoosh\Push;
use meumobi\sitebuilder\repositories\VisitorsRepository;
use meumobi\sitebuilder\entities\Visitor;

require_once 'lib/pushwoosh/Push.php';

class PushNotificationWorker extends Worker
{
	protected $item;

	public function perform()
	{
		$content = $this->getItem()->title;
		$devices = $this->getDevicesTokens();
		$this->logger()->info('Sending push notification', [
			'item id' => $this->getItem()->_id,
			'site id' => $this->getItem()->site_id,
			'content' => $content,
			'devices' => $devices,
		]);
		Push::notify($content, $devices);
	}

	protected function getItem()
	{
		if ($this->item) return $this->item;
		$this->item = Items::find('type', array('conditions' => array(
			'_id' => $this->job()->params['item_id']
		)));
		if (!$this->item) {
			throw new RecordNotFoundException("The item '{$id}' was not found"); 
		}
		return $this->item;
	}

	protected function getSite()
	{
		return \Model::load('Sites')->firstById($this->getItem()->site_id);
	}

	protected function getDevicesTokens()
	{
		$repository = new VisitorsRepository();
		$groups = $this->getItem()->to('array')['groups'];//return Document object on direct access
		if ($groups) {
			$visitors = $repository->findBySiteIdAndGroups($this->getSite()->id, $groups);
		} else {
			$visitors = $repository->findBySiteId($this->getSite()->id);
		}
		return array_reduce($visitors, function($tokens, $visitor) {
			$visitorTokens = [];
			foreach ($visitor->devices() as $device) {
				if ($device->pushId()) $visitorTokens[] = $device->pushId();
			}
			return array_merge($tokens, $visitorTokens);
		},[]);
	}
}

