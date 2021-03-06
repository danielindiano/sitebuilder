<?php

require_once 'lib/utils/FileUpload.php';
require_once 'lib/utils/FileDownload.php';
require_once 'lib/phpthumb/ThumbLib.inc.php';

use meumobi\sitebuilder\Logger;

class Images extends AppModel
{
	const COMPONENT = 'images';

	protected $afterSave = array('fillFields');
	protected $beforeDelete = array('deleteFile', 'updateTimestamps');

	public function upload($model, $image, $attr = array())
	{
		return $this->saveImage('uploadFile', $model, $image, $attr);
	}

	public function download($model, $image, $attr = array())
	{
		return $this->saveImage('downloadFile', $model, $image, $attr);
	}

	public function allByRecord($model, $fk)
	{
		return $this->all(array(
			'conditions' => array(
				'model' => $model,
				'foreign_key' => $fk,
				'visible' => 1
			)
		));
	}

	public function firstByRecord($model, $fk)
	{
		return $this->first(array(
			'conditions' => array(
				'model' => $model,
				'foreign_key' => $fk,
				'visible' => 1
			)
		));
	}

	public function link($size = null)
	{
		$path = String::insert('/:path/:size:filename', array(
			'model' => Inflector::underscore($this->model),
			'filename' => basename($this->path),
			'path' => dirname($this->path),
			'size' => $size ? $size . '_' : ''
		));
		return $path;
	}

	public function regenerate($model)
	{
		$fileInfo = pathinfo($this->path);
		$path = $fileInfo['dirname'];
		$filename = $fileInfo['basename'];
		$this->resizeImage($model, $path, $filename);
	}

	public function toJSONPerformance()
	{
		$imageKeys = array('path', 'title', 'description', 'id');
		$data = $this->data;
		$data['path'] = '/' . $data['path'];
		return array_intersect_key($data, array_flip($imageKeys));
	}

	public function toJSON()
	{
		$data = $this->data;
		$data['path'] = '/' . $data['path'];
		return $data;
	}

	public function getPath($model)
	{
		if (!is_string($model)) {
			$model = $model->imageModel();
		}

		return String::insert('uploads/:model', array(
			'model' => Inflector::underscore($model)
		));
	}

	protected function saveImage($method, $model, $image, $attr)
	{
		if (!$this->transactionStarted()) {
			$transaction = true;
			$this->begin();
		} else {
			$transaction = false;
		}

		try {
			$self = new Images();

			$defaults = array(
				'model' => $model->imageModel(),
				'foreign_key' => $model->id()
			);
			$self->save(array_merge($defaults, $attr));

			$path = $this->getPath($model);
			$filename = $this->{$method}($model, $image);

			$info = $this->getImageInfo($path, $filename);
			$filename = $self->renameTempImage($info);

			$info['path'] = $path . '/' . $filename;
			$self->updateAttributes($info);
			$self->save();

			if ($self->model == 'Items' && $self->foreign_key) {
				$item = \app\models\Items::find('type', array('conditions' => array(
					'_id' => $self->foreign_key
				)));
				$item->modified = date('Y-m-d H:i:s');
				$item->save();
			}

			$this->resizeImage($model, $path, $filename);

			if($transaction) {
				$this->commit();
			}

			return $self;
		} catch (Exception $e) {
			Logger::error(self::COMPONENT, "$method failed", [
				'model' => $model,
				'path' => $path,
				'message' => $e->getMessage(),
				'exception' => $e,
			]);

			if ($transaction) {
				$this->rollback();
			} else {
				$this->delete($self->id);
			}
		}
	}

	protected function uploadFile($model, $image)
	{
		$uploader = new FileUpload();
		$uploader->path = APP_ROOT . '/' . $this->getPath($model);

		return $uploader->upload($image, ':original_name');
	}

	protected function downloadFile($model, $image)
	{
		$downloader = new FileDownload();
		$downloader->path = APP_ROOT . '/' . $this->getPath($model);
		$salt = md5(time());

		return $downloader->download($image, "${salt}_:original_name");
	}

	protected function renameTempImage($info)
	{
		$types = array(
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif'
		);
		$destination = String::insert(':id.:ext', array(
			'id' => $this->id,
			'ext' => $types[$info['type']]
		));
		Filesystem::rename(APP_ROOT . '/' . $info['path'], $destination);

		return $destination;
	}

	protected function resizeImage($model, $path, $filename)
	{
		$fullpath = Filesystem::path(APP_ROOT . '/' . $path . '/' . $filename);
		$resizes = $model->resizes();
		$modes = array(
			'' => 'resize',
			'#' => 'adaptiveResize',
			'!' => 'cropFromCenter'
		);

		foreach ($resizes as $resize) {
			$image = PhpThumbFactory::create($fullpath);

			extract($this->parseResizeValue($resize)); // extracts $resize, $w, $h, $mode
			$method = $modes[$mode];
			$image->{$method}($w, $h);
			$resisedFile = String::insert(':path/:wx:h_:filename', array(
				'path' => Filesystem::path(APP_ROOT . '/' . $path),
				'filename' => $filename,
				'w' => $w,
				'h' => $h
			));

			$image->save($resisedFile);
			chmod($resisedFile, 0777);
		}
	}

	protected function deleteFile($id)
	{
		$self = $this->firstById($id);

		if (!is_null($self->path)) {
			Filesystem::delete(String::insert(APP_ROOT . '/:filename', array(
				'filename' => $self->path
			)));

			$this->deleteResizedFiles($self->model, $self->path);
		}

		return $id;
	}

	protected function updateTimestamps($id)
	{
		$self = $this->firstById($id);

		if ($self->model == 'Items' && $self->foreign_key) {
			$item = \app\models\Items::find('type', array('conditions' => array(
				'_id' => $self->foreign_key
			)));
			$item->modified = date('Y-m-d H:i:s');
			$item->save();
		} else {
			if ($self->model == 'SitePhotos'
				|| $self->model == 'SiteLogos'
				|| $self->model == 'SiteAppleTouchIcon'
				|| $self->model == 'SiteSplashScreens') {
				$model = 'Sites';
			} else {
				$model = $self->model;
			}
			$object = Model::load($model)->firstById($self->foreign_key);
			$object->modified = date('Y-m-d H:i:s');
			$object->save();
		}

		return $id;
	}

	protected function deleteResizedFiles($model, $filename)
	{
		if ($model == 'Items') {
			$model = new \app\models\Items;
			$resizes = $model->resizes();
		} else {
			$model = Model::load($model);
			$resizes = $model->resizes();
		}

		foreach ($resizes as $resize) {
			$values = $this->parseResizeValue($resize);
			Filesystem::delete(String::insert(':path/:wx:h_:filename', array(
				'path' => Filesystem::path(APP_ROOT . '/' . dirname($filename)),
				'filename' => basename($filename),
				'w' => $values['w'],
				'h' => $values['h']
			)));
		}
	}

	protected function parseResizeValue($value)
	{
		preg_match('/^(\d+)x(\d+)(#|!|>|)$/', $value, $options);
		$keys = array('resize', 'w', 'h', 'mode');
		return array_combine($keys, $options);
	}

	protected function getImageInfo($path, $filename)
	{
		$filepath = Filesystem::path(APP_ROOT . '/' . $path . '/' . $filename);
		$image = new Imagick($filepath);
		$size = $image->getImageLength();

		return array(
			'path' => $path . '/' . $filename,
			'type' => $image->getImageMimeType(),
			'filesize' => $size,
			'filesize_octal' => decoct($size)
		);
	}

	protected function fillFields()
	{
		$schema = array_keys($this->schema());
		$self = array_keys($this->data);
		$diff = array_diff($schema, $self);

		foreach ($diff as $i) {
			$this->data[$i] = null;
		}
	}

	public function __toString()
	{
		return $this->path;
	}
}

class ImageNotFoundException extends Exception {}
