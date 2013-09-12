<?php


class LieMapper {

	protected $_db;

	public function __construct($db) {

		if (empty($db) || !is_object($db)) {
			throw new Exception("DB connection is not properly set.");
		}

		$this->_db = $db;
	}

	/**
	 * 
	 * @return LieEntity[] 
	 */
	public function getAll() {

		$sth = $this->_db->prepare("SELECT id, description, date, user_id, valid FROM lies");
		$sth->execute();
		$rows = $sth->fetchAll();

		return array_map(array($this, 'createEntityFromMap'), $rows);
	}


	/**
	 * @return LieEntity
	 */
	public function createEntityFromMap($lie) {

		$entity = new LieEntity();
		foreach ($lie as $key => $value) {
			$entity->{$key} = $value;
		}

		return $entity;
	}

}