<?php

require_once dirname(__DIR__) . '/lib/LieMapper.php';
require_once dirname(__DIR__) . '/lib/LieEntity.php';
require_once dirname(__DIR__) . '/tests/PDOMock.php';

class LieMapperTest extends PHPUnit_Framework_TestCase 
{


	/**
	 *
	 * @test 
	 * @expectedException Exception_InvalidDb
	 */
	public function checkExceptionIsThrownWhenDbConnectionIsEmpty() {

		$lieMapper = new LieMapper(array());
	}

	/**
	 * @test
	 */
	public function returnsLieCollection() 
	{	

		/*
		  Given I have a collection of expected lies
		  When I create a database connection
		  and pass it to my LieMapper
		  When I eecute getAll()
		  Then I get my expected collection of lies.
		 */

		  //create our collection of lies
		  $lieInfo = array(
		  		array(
		  			'id' => uniqid(),
		  			'date_created' => time(),
		  			'description' => 'First test lie',
		  			'user_id' => uniqid(),
		  			'valid' => 1
		  			),

		  		array(
		  			'id' => uniqid(),
		  			'date_created' => time(),
		  			'description' => 'Second test lie',
		  			'user_id' => uniqid(),
		  			'valid' => 1
		  			),
		  		
		  		array(
		  			'id' => uniqid(),
		  			'date_created' => time(),
		  			'description' => 'Third test lie',
		  			'user_id' => uniqid(),
		  			'valid' => 0
		  			),
		  	);



		  //create a collection of lie objects
		  $expectedLies = array();
		  $expectedLies[0] = new LieEntity();
		  $expectedLies[1] = new LieEntity();
		  $expectedLies[2] = new LieEntity();

		  foreach ($lieInfo as $idx => $details) {
		  	$expectedLies[$idx]->id = $details['id'];
		  	$expectedLies[$idx]->date_created = $details['date_created'];
		  	$expectedLies[$idx]->description = $details['description'];
		  	$expectedLies[$idx]->user_id = $details['user_id'];
		  	$expectedLies[$idx]->valid = $details['valid'];
		  }

		  //create the PDO mock
		  $sth = $this->getMockBuilder('stdClass')
		  			  ->setMethods(array('execute', 'fetchAll'))
		  			  ->getMock();

		  $sth->expects($this->once())
		  	  ->method('fetchAll')
		  	  ->will($this->returnValue($lieInfo));

		  $db = $this->getMockBuilder('PDOMock')
		  	          ->setMethods(array('prepare'))
		  	          ->getMock();

		  $db->expects($this->once())
		     ->method('prepare')
		     ->with($this->stringContains('SELECT id, description, date_created, user_id, valid FROM lies'))
		     ->will($this->returnValue($sth));

		   $lieMapper = new LieMapper($db);
		   $lies      = $lieMapper->getAll();
		   $this->assertEquals($expectedLies, 
		   					  $lies,
		   					  "LieMapper::getAll() did not return expected collection");
	}


	/**
	 * 
	 * @test
	 */
	public function returnASingleLieRecordForKnownRecord() 
	{

		/*
			Given a lie entity record,
			when I call the get(id) method of the LieMapper to get a specific record,
			I should be able to get the same lie entity.

		 */

		$lieArr = array(
				'id' => uniqid(),
				'description' => 'Sample Lie Entity #1',
				'date_created' => time(),
				'user_id' => uniqid(),
				'valid' => 1
			);
  
	    $lieExpected = new LieEntity();
	    $lieExpected->id = $lieArr['id'];
	    $lieExpected->description = $lieArr['description'];
	    $lieExpected->date_created = $lieArr['date_created'];
	    $lieExpected->user_id = $lieArr['user_id'];
	    $lieExpected->valid   = $lieArr['valid'];

	    $sth = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'fetch', 'rowCount'))
	                ->getMock();

	    $sth->expects($this->once())
	        ->method('fetch')
	        ->will($this->returnValue($lieArr));

	    $sth->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(1));

	    // $sth->expects($this->once())
	    //     ->method('execute')
	    //     ->with($this->)

	    $db  = $this->getMockBuilder('PDOMock')
	                ->setMethods(array('prepare'))
	                ->getMock();
	    $db->expects($this->once())
	       ->method('prepare')
	       ->with($this->stringContains('SELECT id, description, date_created, user_id, valid FROM lies WHERE id'))
	       ->will($this->returnValue($sth));

	    $lieMapper = new LieMapper($db);
	    $lie = $lieMapper->get($lieExpected->id);
	    $this->assertEquals($lieExpected, 
	    					$lie,
	    					"LieMapper::get() did not return the expected data for existing record");
	}



	/**
	 * 
	 * @test
	 */
	public function returnASingleLieRecordForInvalidRecord() 
	{

		/*
			Given a lie entity record,
			when I call the get(id) method of the LieMapper to get a specific record,
			if should be able to get a response (false) if the record does not exist.

		 */

	    $sth = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(0));

	    $db  = $this->getMockBuilder('PDOMock')
	                ->setMethods(array('prepare'))
	                ->getMock();
	    $db->expects($this->once())
	       ->method('prepare')
	       ->with($this->stringContains('SELECT id, description, date_created, user_id, valid FROM lies WHERE id'))
	       ->will($this->returnValue($sth));

	    $lieMapper = new LieMapper($db);
	    $lie = $lieMapper->get("UNKNOWNID123");
	    $this->assertEquals(false, 
	    					$lie,
	    					"LieMapper::get() did not return the expected data for invalid record");
	}

	public function testDeleteExistingRecord() 
	{

		/*
			Given a lie entity record,
			check if the record exists
			if exist, return true
		 */


	    $lie = new LieEntity();
	    $lie->id = uniqid();
	    $lie->description = "Sample Lie Entity #1";
	    $lie->date_created = time();
	    $lie->user_id = uniqid();
	    $lie->valid   = 1;

	    $sth1 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth1->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(1));


	    $sth2 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth2->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(1));

	    $db  = $this->getMockBuilder('PDOMock')
	                ->setMethods(array('prepare'))
	                ->getMock();

	    $db->expects($this->at(0))
	       ->method('prepare')
	       ->with($this->stringContains('SELECT id FROM lies WHERE id'))
	       ->will($this->returnValue($sth1));

	    $db->expects($this->at(1))
	       ->method('prepare')
	       ->with($this->stringContains('DELETE FROM lies WHERE id'))
	       ->will($this->returnValue($sth2));

	     $lieMapper = new LieMapper($db);
	     $response = $lieMapper->delete($lie->id);

	     $this->assertEquals(true,
	     				   $response,
	     				   "LieMapper::delete() did not return the expected result on existing");

	}



	/**
	 *
	 * @test
	 */
	public function testDeleteNonExistentRecord() 
	{

		/*
			Given a lie entity record,
			check if the record exists
			if not exist, return false
		 */


	    $sth1 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth1->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(0));


	    $db  = $this->getMockBuilder('PDOMock')
	                ->setMethods(array('prepare'))
	                ->getMock();

	    $db->expects($this->at(0))
	       ->method('prepare')
	       ->with($this->stringContains('SELECT id FROM lies WHERE id'))
	       ->will($this->returnValue($sth1));

	     $lieMapper = new LieMapper($db);
	     $response = $lieMapper->delete("ABCDEF1239232");

	     $this->assertEquals(false,
	     				   $response,
	     				   "LieMapper::delete() did not return the expected result on non-existing record");

	}	


	/**
	 *
	 * @test
	 */
	public function testDeleteExistingRecordWithZeroAffectedRecordCount() 
	{

		/*
			Given a lie entity record,
			check if the record exists
			if exist
			try to delete
			and read the rows affected (of the delete)
			return false if the rows affected is zero
			otherwise return true
		 */


	    $sth1 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth1->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(1));


	    $sth2 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth2->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(0));

	    $db  = $this->getMockBuilder('PDOMock')
	                ->setMethods(array('prepare'))
	                ->getMock();

	    $db->expects($this->at(0))
	       ->method('prepare')
	       ->with($this->stringContains('SELECT id FROM lies WHERE id'))
	       ->will($this->returnValue($sth1));

	    $db->expects($this->at(1))
	       ->method('prepare')
	       ->with($this->stringContains('DELETE FROM lies WHERE id'))
	       ->will($this->returnValue($sth2));


	     $lieMapper = new LieMapper($db);
	     $response = $lieMapper->delete("SomeExistingRecordId");

	     $this->assertEquals(false,
	     				   $response,
	     				   "LieMapper::delete() did not return the expected result on existing record with rowsaffected zero");

	}	


	/**
	 * 
	 *  @test
	 */
	public function createDuplicateRecord() {


		$lieArr = array(
				'id' => uniqid(),
				'description' => 'Sample Lie',
				'date_created' => time(),
				'user_id' => uniqid(),
				'valid' => 1
			);

	    $entity = new LieEntity();
	    $entity->id = $lieArr['id'];
	    $entity->description = $lieArr['description'];
	    $entity->date_created = $lieArr['date_created'];
	    $entity->user_id = $lieArr['user_id'];
	    $entity->valid = $lieArr['valid'];
	    



	    $sth1 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount', 'fetch'))
	                ->getMock();

	    $sth1->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(1));

	    $sth1->expects($this->once())
	        ->method('fetch')
	        ->will($this->returnValue($lieArr));

	    $db  = $this->getMockBuilder('PDOMock')
	                ->setMethods(array('prepare'))
	                ->getMock();

	    $db->expects($this->at(0))
	       ->method('prepare')
	       ->with($this->stringContains('SELECT id, description, date_created, user_id, valid FROM lies WHERE id'))
	       ->will($this->returnValue($sth1));

	    $lieMapper = new LieMapper($db);
	    $response = $lieMapper->create($entity);
	    $this->assertEquals(false, $response, "LieMapper::create() did not return the expected result when there's an existing record");

	}



	/**
	 * 
	 *  @test
	 */
	public function createANewRecord() {


	    $entity = new LieEntity();
	    $entity->id = uniqid();   
	    $entity->description = "This is a sample Lie";
	    $entity->date_created = time();
	    $entity->user_id = uniqid();
	    $entity->valid = true;


	    $sth1 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth1 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount', 'fetch'))
	                ->getMock();

	    $sth1->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(0));



	    $sth2 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth2->expects($this->once())
	         ->method('rowCount')
	         ->will($this->returnValue(1));

	    $db  = $this->getMockBuilder('PDOMock')
	                ->setMethods(array('prepare'))
	                ->getMock();

	    $db->expects($this->at(0))
	       ->method('prepare')
	       ->with($this->stringContains('SELECT id, description, date_created, user_id, valid FROM lies WHERE id'))
	       ->will($this->returnValue($sth1));

		$db->expects($this->at(1))
		   ->method('prepare')
		   ->with($this->stringContains('INSERT INTO lies (id, description, date_created, user_id, valid) VALUES'))
		   ->will($this->returnValue($sth2));

	    $lieMapper = new LieMapper($db);
	    $response = $lieMapper->create($entity);
	    $this->assertEquals(true, $response, "LieMapper::create() did not return the expected result adding a new record");

	}	



	/**
	 * 
	 *  @test
	 */
	public function createANewRecordThatFailedOnInsert() {


	    $entity = new LieEntity();
	    $entity->id = uniqid();   
	    $entity->description = "This is a sample Lie";
	    $entity->date_created = time();
	    $entity->user_id = uniqid();
	    $entity->valid = true;


	    $sth1 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth1 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount', 'fetch'))
	                ->getMock();

	    $sth1->expects($this->once())
	        ->method('rowCount')
	        ->will($this->returnValue(0));



	    $sth2 = $this->getMockBuilder('stdClass')
	                ->setMethods(array('execute', 'rowCount'))
	                ->getMock();

	    $sth2->expects($this->once())
	         ->method('rowCount')
	         ->will($this->returnValue(0));

	    $db  = $this->getMockBuilder('PDOMock')
	                ->setMethods(array('prepare'))
	                ->getMock();

	    $db->expects($this->at(0))
	       ->method('prepare')
	       ->with($this->stringContains('SELECT id, description, date_created, user_id, valid FROM lies WHERE id'))
	       ->will($this->returnValue($sth1));

		$db->expects($this->at(1))
		   ->method('prepare')
		   ->with($this->stringContains('INSERT INTO lies (id, description, date_created, user_id, valid) VALUES'))
		   ->will($this->returnValue($sth2));

	    $lieMapper = new LieMapper($db);
	    $response = $lieMapper->create($entity);
	    $this->assertEquals(false, $response, "LieMapper::create() did not return the expected result adding a new record that failed during the INSERT");

	}		

}
