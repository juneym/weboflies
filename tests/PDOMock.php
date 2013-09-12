<?php

//PDO cannot be serialized so having
//this PDOMock clas should be enough for testing and mocking purposes.

class PDOMock extends \PDO {

	public function __construct() {

	}
}
