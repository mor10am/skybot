<?php

namespace Skybot;

class Storage extends \SQLite3
{

	public function __construct($filename)
	{
		parent::__construct($filename);

		$sql = 'CREATE  TABLE  IF NOT EXISTS "main"."brain" ("field" VARCHAR NOT NULL , "skypename" VARCHAR NOT NULL , "value" VARCHAR, PRIMARY KEY ("field", "skypename"))';

		$rs = $this->query($sql);
	}

	public function set($skypename, $field, $value)
	{
		$field = mb_strtolower($field);

		$sql = sprintf("insert or replace into brain (field, skypename, value) values ('%s', '%s', '%s')", $field, $skypename, $value);

		$this->exec($sql);
	}

	public function get($skypename, $field)
	{
		$field = mb_strtolower($field);

		$sql = sprintf("select value from brain where field = '%s' and skypename = '%s'", $field, $skypename);

		$rs = $this->query($sql);

		$data = $rs->fetchArray();

		return $data['value'];
	}
}