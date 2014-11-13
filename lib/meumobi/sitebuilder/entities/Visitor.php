<?php

namespace meumobi\sitebuilder\entities;

use lithium\util\Inflector;

use MongoId;
use Security;

class Visitor
{
	protected $id;
	protected $email;
	protected $hashedPassword;
	protected $lastLogin;
	protected $devices = array();
	protected $groups = array();

	public function __construct(array $attrs = [])
	{
		$this->setAttributes($attrs);
	}

	public function setAttributes(array $attrs)
	{
		foreach ($attrs as $key => $value) {
			$key = Inflector::camelize($key, false);
			$method = 'set' . Inflector::camelize($key);
			if (method_exists($this, $method)) {
				$this->$method($value);
			} else if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}
	}

	public function id()
	{
		return $this->id ? $this->id->{'$id'} : null;
	}

	public function setId(MongoId $id)
	{
		$this->id = $id;
	}

	public function email()
	{
		return $this->email;
	}

	public function setPassword($password)
	{
		if (!empty($password) {
			return $this->hashedPassword = Security::hash($password, 'sha1');
		}
	}

	public function hashedPassword()
	{
		return $this->hashedPassword;
	}

	public function lastLogin()
	{
		return $this->lastLogin;
	}

	public function devices()
	{
		return $this->devices;
	}

	public function groups()
	{
		return $this->groups;
	}
}
