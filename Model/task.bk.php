<?php
class Task extends TasksAppModel {

	var $name = 'Task';
	var $actsAs = array('Tree');
	var $validate = array(
		'name' => array('notempty'),
	); 
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
		'ParentTask' => array(
			'className' => 'Tasks.Task',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Assignee' => array(
			'className' => 'Users.User',
			'foreignKey' => 'assignee_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
			),
		'Creator' => array(
			'className' => 'Users.User',
			'foreignKey' => 'creator_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
			),
		);
	
	var $hasMany = array(
		'ChildTask' => array(
			'className' => 'Tasks.Task',
			'foreignKey' => 'parent_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
			),
		);
	
	/*function __construct($id = false, $table = null, $ds = null) {
    	parent::__construct($id, $table, $ds);
	    $this->virtualFields['displayName'] = sprintf('CONCAT(%s.name, " ", %s.description)', $this->alias, $this->alias);
		$this->displayField = 'displayName';
    }*/
	
	function add($data) {
		$data = $this->cleanData($data);
		if ($this->save($data)) : 
			return true;
		else : 
			return false;
		endif;
	}
	
	
	function complete($data) {
		$data['Task']['is_completed'] = 1;
		$data['Task']['completed_date'] = date('Y-m-d h:i:s');
		if($this->save($data)) : 
			return true;
		else :
			return false;
		endif;
	}
	
	
	function incomplete($data) {
		$data['Task']['is_completed'] = 0;
		if($this->save($data)) : 
			return true;
		else :
			return false;
		endif;
	}
	
	function cleanData($data) {
		if (empty($data['Task']['name']) && !empty($data['Task']['description'])) :
			$data['Task']['name'] = $data['Task']['description'];
		endif;
		
		return $data;
	}
	
	
	function view($id = null, $params = null) {
		$task = $this->find('first', array(
			'conditions' => array(
				'or' => array(
					'Task.id' => $id,
					),
				),
			$params,
			));
		
		if (empty($task)) {
			throw new Exception(__d('tasks', 'Invalid Task', true));
		} else {
			$plugin = pluginize($task['Task']['model']);
			$model = $task['Task']['model'];
			$init = !empty($plugin) ? $plugin . '.' . $model : $model;
			$foreignKey = $task['Task']['foreign_key'];
			$result = ClassRegistry::init($init)->find('first', array('conditions' => array($model.'.id' => $foreignKey)));
			
			$task['Associated'] = $result;
		}
		return $task;
	}
}
?>