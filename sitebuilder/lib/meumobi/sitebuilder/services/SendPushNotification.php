<?php

namespace meumobi\sitebuilder\services;

use Config;
use MeuMobi;
use app\models\Items;
use meumobi\sitebuilder\Logger;
use meumobi\sitebuilder\repositories\DevicesRepository;
use meumobi\sitebuilder\repositories\VisitorsRepository;
use meumobi\sitebuilder\services\SendOneSignalNotification;
use meumobi\sitebuilder\services\SendPushwooshNotification;

class SendPushNotification
{
	const COMPONENT = 'pushnotif';

	public function perform($item)
	{
		$site = $item->site();
		$category = $item->parent();
		$devices = $this->getDevicesTokens($item, $site);
		$service = $site->pushnotif_service == 'pushwoosh'
			? new SendPushwooshNotification
			: new SendOneSignalNotification;

		if (!$site->pushnotif_app_id || !$category->notification) {
			return;
		}

		$log = [
			'item_id' => $item->id(),
			'category_id' => $category->id,
			'site_id' => $site->id,
		];

		if (!$devices) {
			Logger::info(self::COMPONENT, 'no devices found. no push notification will be sent', $log);
			return;
		}

		Logger::info(self::COMPONENT, 'sending push notification', $log + [
			'content' => $item->title,
			'number_of_devices' => count($devices),
		]);

		$app = [
			'appId' => $site->pushnotif_app_id,
			'appAuthToken' => $site->pushnotif_app_auth_token,
		];

		$icon = $site->appleTouchIcon()
			? MeuMobi::url($site->appleTouchIcon()->link('72x72'), true)
			: null;

		$banner = $item->images()
			? MeuMobi::url($item->images()[0]->link('314x220'), true)
			: null;

		$notif = [
			'header' => $site->title,
			'content' => $item->title,
			'banner' => $banner,
			'icon' => $icon,
			'data' => [
				'item_id' => $item->id(),
				'category_id' => $item->parent_id,
			],
			'devices' => $devices,
		];

		Logger::info(self::COMPONENT, 'delegating to push notif service', $log + [
			'parameters' => $notif,
		]);

		$notification_response = $service->perform($app, $notif);

		if ($notification_response !== false) {
			if ($site->pushnotif_service=='onesignal' && isset($notification_response['notification_id'])){
				$item->notification_id = $notification_response['notification_id'];
				$item->save();
			}
			Logger::info(self::COMPONENT, 'push notification sent', $log);
		}
	}

	protected function getDevicesTokens($item, $site)
	{
		$visitors = null;

		if ($site->private) {
			$groups = $item->to('array')['groups'];
			$visitorsRepo = new VisitorsRepository();
			$visitors = $groups
				? array_map(function($v) {
					return $v->id();
				}, $visitorsRepo->findBySiteIdAndGroups($site->id, $groups))
				: null;
		}

		$repository = new DevicesRepository();
		$devices = $repository->findForPushNotif($site->id, $visitors);

		return array_map(function($device) { return $device->pushId(); }, $devices);
	}
}
