<?php
App::uses('TasksAppModel', 'Tasks.Model');


/**
 * Model to control models attached to a task
 * 
 */


class TaskAttachment extends TasksAppModel {

	public $name = 'TaskAttachment';
	
	/**
	 * Attachable Models
	 * Array of Models
	 */
 	public $attachable = array(
 		'Answer',
	);
	
	public $belongsTo = array(
		'Task' => array(
			'className' => 'Tasks.Task',
			'foreignKey' => 'task_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		));
}