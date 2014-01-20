<?php
App::uses('Task', 'Tasks.Model');

/**
 * Task Test Case
 *
 */
class TaskTestCase extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Tasks.Task',
		'plugin.Users.User',
		'plugin.Users.Used',
		'plugin.Media.Media',
		'plugin.Media.MediaAttachment'
		);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Task = ClassRegistry::init('Tasks.Task');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Task);
		parent::tearDown();
	}

/**
 * Test save
 */
	public function testSave() {
		$data = array(
			'Task' => array(
				'model' => 'Task',
				'foreign_key' => '529f7433-62d4-44d6-8ee8-621a0ad25527',
				'assignee_id' => '2',
				'name' => 'Bally\'s'
			),
			'ChildTask' => array(
				array(
					'model' => 'Task',
					'assignee_id' => '2',
					'name' => 'Visit Bally\'s',
					'due_date' => '2014-01-19'
				)
			)
		);
		$this->Task->create();
		$result = $this->Task->saveAll($data);
		$this->assertTrue($result);
	}
	
}
