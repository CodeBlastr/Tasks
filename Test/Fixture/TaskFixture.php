<?php
/**
 * TaskFixture
 *
 */
class TaskFixture extends CakeTestFixture {
	
/**
 * Import
 *
 * @var array
 */
	public $import = array('config' => 'Tasks.Task');

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'model' => 'Task',
			'foreign_key' => '529f7433-62d4-44d6-8ee8-621a0ad25527',
			'assignee_id' => '2',
			'parent_id' => null,
			'lft' => 1,
			'rght' => 2,
			'name' => 'Bally\'s',
			'due_date' => '2014-01-19'
		)
	);
}
