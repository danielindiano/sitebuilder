<?php
namespace pushwoosh;

use Config;
use Exception;
use MeuMobi;
use Gomoob\Pushwoosh\Client\Pushwoosh;
use Gomoob\Pushwoosh\Model\Notification\Android;
use Gomoob\Pushwoosh\Model\Notification\IOS;
use Gomoob\Pushwoosh\Model\Notification\Notification;
use Gomoob\Pushwoosh\Model\Request\CreateMessageRequest;
use meumobi\sitebuilder\Logger;
use meumobi\sitebuilder\validators\ParamsValidator;

class Push
{
	protected static $client;

	public static function notify(array $options)
	{
		list($site, $item, $devices) = ParamsValidator::validate($options, [
			'site', 'item', 'devices']);

		// CreateMessageRequest:
		// http://gomoob.github.io/php-pushwoosh/create-message.html
		$request = CreateMessageRequest::create()
			->addNotification(static::getNotification($site, $item, $devices));

		$response = static::getClient($site->pushwoosh_app_id)
			->createMessage($request);

		Logger::debug('push_notification', 'payload request', $request->toJSON());

		if ($response->isOk()) {
			return [
				'status_code' => $response->getStatusCode(),
				'status_message' => $response->getStatusMessage()
			];
		} else {
			throw new Exception("Error sending push notification, "
				. "status_code: {$response->getStatusCode()}, "
				. "status_message: {$response->getStatusMessage()}"
			);
		}
	}

	public static function getNotification($site, $item, $devices)
	{
		$android = Android::create()
			->setHeader($site->title);

		if ($icon = $site->appleTouchIcon()) {
			$android->setIcon(MeuMobi::url($icon->link('72x72'), true));
		}

		if ($thumbnails = $item->thumbnails->to('array')) {
			$android->setBanner($thumbnails[0]['url']);
		}

		$notification = Notification::create()
			->setContent($item->title)
			->setData([
				'item_id' => $item->id(),
				'category_id' => $item->category_id,
			])
			->setIOS(IOS::create()->setBadges('+1'))
			->setAndroid($android);

		if ($devices) $notification->setDevices($devices);

		return $notification;
	}

	public static function getClient($app)
	{
		if (static::$client) return static::$client;

		return static::$client = Pushwoosh::create()
			->setApplication($app)
			->setAuth(Config::read('PushWoosh.authToken'));
	}
}
