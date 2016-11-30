<?php

namespace meumobi\sitebuilder\services;

use Config;
use Exception;
use OneSignal\Config as OneSignalConfig;
use OneSignal\Exception\OneSignalException;
use OneSignal\OneSignal;
use meumobi\sitebuilder\Logger;
use meumobi\sitebuilder\validators\ParamsValidator;

class SendOneSignalNotification
{
	const COMPONENT = 'pushnotif_onesignal';

	public function perform(array $app, array $notif)
	{
		list($appId, $appAuthToken) = ParamsValidator::validate($app,
			['appId', 'appAuthToken']);

		$config = new OneSignalConfig();
		$config->setApplicationId($appId);
		$config->setApplicationAuthKey($appAuthToken);
		$config->setUserAuthKey(Config::read('OneSignal.authToken'));

		$api = new OneSignal($config);

		$notification = $this->notification($notif);

		Logger::debug(self::COMPONENT, 'payload request', $notification);

		try {
			$api->notifications->add($notification);
			
			return true;
		} catch (OneSignalException $e) {
			Logger::error(self::COMPONENT, 'push notification not sent', [
				'app' => $app,
				'notifcation' => $notif,
				'status_code' => $e->getStatusCode(),
				'errors' => $e->getErrors(),
			]);

			return false;
		}
	}

	protected function notification(array $options)
	{
		list($header, $content, $banner, $icon, $data, $devices) =
			ParamsValidator::validate($options, [
				'header', 'content', 'banner', 'icon', 'data', 'devices'
			]);

		$notification = [
			'headings' => [ 'en' => $header ],
			'contents' => [ 'en' => $content ],
			'data' => $data,
			'ios_badgeType' => 'Increase',
			'ios_badgeCount' => 1,
		];

		if ($devices) {
			$notification['include_player_ids'] = $devices;
		}

		if ($banner) {
			$notification['big_picture'] = $banner;
		}

		if ($icon) {
			$notification['large_icon'] = $icon;
		}

		return $notification;
	}
}
