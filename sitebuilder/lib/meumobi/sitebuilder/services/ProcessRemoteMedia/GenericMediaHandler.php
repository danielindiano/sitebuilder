<?php

namespace meumobi\sitebuilder\services\ProcessRemoteMedia;

use Config;
use Filesystem;
use Mimey\MimeTypes;
use lithium\net\http\Response;
use meumobi\sitebuilder\Logger;

class GenericMediaHandler
{
	const COMPONENT = 'remote_media.generic_media_handler';

	public function match($url)
	{
		return 1;
	}

	public function perform($url)
	{
		$info = [];
		$headers = '';

		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RANGE, '0-0');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, function($curl, $data) use (&$headers) {
			$headers .= $data;
			$length = strlen($data);
			return $length > 2 ? $length : 0;
		});

		curl_exec($curl);
		curl_close($curl);

		$response = new Response([
			'message' => $headers,
		]);

		$status = $response->status['code'];
		$info['type'] = $this->getFileType($url, $response);
		$info['extension'] = $this->getFileExtension($url, $info['type']);

		if (($length = $response->headers('Content-Length')) > 1) {
			$info['length'] = (int) $length;
		} else if (preg_match('/bytes 0-0\/(\d+)/', $response->headers('Content-Range'), $matches)) {
			$info['length'] = (int) $matches[1];
		}

		return [
			($status >= 200 && $status < 300 ? $info : null),
			"http status $status"
		];
	}

	protected function getFileType($url, $response)
	{
		$type = null;
		
		if ($response->headers('Content-Type')) {
			list($type) = explode(';', $response->headers('Content-Type'));
		}

		if ($type == 'application/octet-stream' && $response->headers('Content-Disposition')) {
			$disposition = $response->headers('Content-Disposition');
			$match = preg_match('/filename="?([^"]+)"?/', $disposition, $matches);

			if ($match) {
				$type = $this->getFileTypeFromFilename($matches[1]);
			}
		}

		if (!$type) {
			$type = $this->getFileTypeFromFilename(basename($url));
		}

		return $type;
	}

	protected function getFileTypeFromFilename($filename)
	{
		$extension = Filesystem::extension($filename);
		$mimes = new MimeTypes;

		return $mimes->getMimeType($extension);
	}

	protected function getFileExtension($url, $type)
	{
		$actualExt = Filesystem::extension(basename($url));

		$mimes = new MimeTypes;
		$extensions = $mimes->getAllExtensions($type);
		$blacklist = Config::read('FileExtensions.blacklist');
		
		$allowedExtensions = array_values(array_diff($extensions, $blacklist));

		if (in_array($actualExt, $allowedExtensions)) {
			return $actualExt;
		}

		if (!empty($allowedExtensions[0])) {
			return $allowedExtensions[0];			
		}

		return null;

	}
}
