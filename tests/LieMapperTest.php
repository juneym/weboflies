<?php

require_once dirname(__DIR__) . '/lib/LieMapper.php';
require_once dirname(__DIR__) . '/lib/LieEntity.php';
require_once dirname(__DIR__) . '/tests/PDOMock.php';

class LieMapperTest extends PHPUnit_Framework_TestCase 
{

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
		  			'date' => time(),
		  			'description' => 'First test lie',
		  			'user_id' => uniqid(),
		  			'valid' => 1
		  			),

		  		array(
		  			'id' => uniqid(),
		  			'date' => time(),
		  			'description' => 'Second test lie',
		  			'user_id' => uniqid(),
		  			'valid' => 1
		  			),
		  		
		  		array(
		  			'id' => uniqid(),
		  			'date' => time(),
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
		  	$expectedLies[$idx]->date = $details['date'];
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
		     ->with($this->stringContains('SELECT id, description, date, user_id, valid FROM lies'))
		     ->will($this->returnValue($sth));


		   $lieMapper = new LieMapper($db);
		   $lies      = $lieMapper->getAll();
		   $this->assertEquals($expectedLies, 
		   					  $lies,
		   					  "LieMapper::getAll() did not return expected collection");
	}

}
