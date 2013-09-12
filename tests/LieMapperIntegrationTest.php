<?php 


require_once dirname(__DIR__) . '/lib/LieMapper.php';
require_once dirname(__DIR__) . '/lib/LieEntity.php';

class LieMapperIntegrationTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * @var PDO
	 */
	protected $_db;

	public function setUp() 
	{
		$this->_db = new PDO("mysql:dbname=weboflies;host=127.0.0.1", "weboflies", "secret");
	}

	public function tearDown() 
	{
		unset($this->_db);
	}


	/**
	 * 
	 * @test
	 */
	public function databaseIsTransactional() 
	{
		$this->assertEquals(true, $this->_db->beginTransaction(), 
							"Database is not transactional");
	}

	/**
	 *
	 * @test
	 */
	public function createLieRecord() 
	{

		$lieMapper = new LieMapper($this->_db);
		$lie = new LieEntity();
		$lie->id = uniqid();
		$lie->description = "This is a sample lie from " . __METHOD__ . " at line " . __LINE__;
		$lie->date_created = time();
		$lie->user_id = "user1";
		$lie->valid = true;

		$created = $lieMapper->create($lie);
		$this->assertEquals(true, $created,
							"LieMapper::create() did not return the expected result for new lie record");

		$lie2 = $lieMapper->get($lie->id);
		$this->assertEquals($lie, $lie2, "Created vs retrieve record doesn't match");


		$result = $lieMapper->delete($lie->id);
		$this->assertEquals(
					true, 
					$result, 
					"lLieMapper::delete() did return the expected response"
				);
	}


	/**
	 *
	 * @test
	 */
	public function createDuplicateLieRecord() {
		$lieMapper = new LieMapper($this->_db);
		$lie = new LieEntity();
		$lie->id = uniqid();
		$lie->description = "This is a sample lie from " . __METHOD__ . " at line " . __LINE__;
		$lie->date_created = time();
		$lie->user_id = "user1";
		$lie->valid = true;

		$created = $lieMapper->create($lie);
		$this->assertEquals(true, $created,
							"LieMapper::create() did not return the expected result for new lie record");

		$lie2 = $lieMapper->get($lie->id);
		$this->assertEquals($lie, $lie2, "Created vs retrieve record doesn't match");


		$lie2 = clone $lie;
		$created = $lieMapper->create($lie2);
		$this->assertEquals(false, $created,
							"LieMapper::create() did not return the expected result for duplicate lie record");

		$result = $lieMapper->delete($lie->id);
		$this->assertEquals(
					true, 
					$result, 
					"lLieMapper::delete() did return the expected response"
				);
	}

}