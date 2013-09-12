<?php

require_once "Exception/InvalidDb.php";

class LieMapper {

	protected $_db;

	public function __construct($db) {

		if (empty($db) || !is_object($db)) {
			throw new Exception_InvalidDb("DB connection is not properly set.");
		}

		$this->_db = $db;
	}

	/**
	 * 
	 * @return LieEntity[] 
	 */
	public function getAll() 
	{

		$sth = $this->_db->prepare("SELECT id, description, date_created, user_id, valid FROM lies");
		$sth->execute();
		$rows = $sth->fetchAll();

		return array_map(array($this, 'createEntityFromMap'), $rows);
	}

	/**
	 *
	 *  @return LieEntity
	 */
	public function get($id)  
	{

		$sth = $this->_db->prepare("SELECT id, description, date_created, user_id, valid FROM lies WHERE id = :id");
		$sth->execute(array('id' => $id));

		if ($sth->rowCount() == 0) {
			return false;
		}

		$row = $sth->fetch();

		$lieEntity = new LieEntity();
		$lieEntity->id           = $row['id'];
		$lieEntity->description  = $row['description'];
		$lieEntity->date_created = $row['date_created'];
		$lieEntity->user_id = $row['user_id'];
		$lieEntity->valid = $row['valid'];

		return $lieEntity;
	}


	/**
	 *
	 * @return boolean
	 */
	public function delete($id)
	{

		$sth = $this->_db->prepare("SELECT id FROM lies WHERE id = :id");
		$sth->execute(array('id' => $id));
		if ($sth->rowCount() == 0) {
			return false;
		}

		$sth = $this->_db->prepare("DELETE FROM lies WHERE id = :id");
		$sth->execute(array('id' => $id));

		if ($sth->rowCount() == 0) {
			return false;
		}

		return true;
	}


	/**
	 * @return boolean
	 */
	public function create(LieEntity $lieEntity) 
	{

		if ($this->get($lieEntity->id) != false) {
			return false;
		}

		$sth = $this->_db->prepare(
						"INSERT INTO lies (id, description, date_created, user_id, valid) VALUES(?, ?, ?, ?, ?)"
						);

		$sth->execute(array(
				$lieEntity->id, 
				$lieEntity->description,
				$lieEntity->date_created,
				$lieEntity->user_id,
				$lieEntity->valid
			));

		if ($sth->rowCount() == 0) {
			return false;
		}

		return true;
	}


	/**
	 * @return LieEntity
	 */
	public function createEntityFromMap($lie) 
	{

		$entity = new LieEntity();
		foreach ($lie as $key => $value) {
			$entity->{$key} = $value;
		}

		return $entity;
	}

}