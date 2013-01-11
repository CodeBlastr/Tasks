<?php
App::uses('TasksAppModel', 'Tasks.Model');

class Task extends TasksAppModel {

	public $name = 'Task';
	public $actsAs = array('Tree');
	public $validate = array(
		'name' => array('notempty'),
	); 
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed
	public $belongsTo = array(
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
		'Project' => array(
			'className' => 'Projects.Project',
			'foreignKey' => 'foreign_key',
			'conditions' => '',
			'fields' => '',
			'order' => ''
			),
		);
	
	public $hasOne = array(
		'Gallery' => array(
			'className' => 'Galleries.Gallery',
			'foreignKey' => 'foreign_key',
			'dependent' => false,
			'conditions' => array('Gallery.model' => 'Task'),
			'fields' => '',
			'order' => ''
			),
		);	
		
	public $hasMany = array(
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
	
	
/**
 * After Find Callback
 * 
 */
	public function afterFind($results, $primary = false) {
	    return $this->triggerOriginCallback('origin_afterFind', $results, $primary); 
	}


/**
 * add a task
 */
	public function add($data) {
		$data = $this->cleanData($data);
		
		if ($this->save($data)) {
			$this->galleryData($data);
			return true;
		} else {
			return false;
		}
	}
	
/**
 * galleryData saves the gallery if there is GalleryImage data present 
 *
 * return bool
 */
	public function galleryData($data) {
		$data['Gallery']['model'] = $this->name;
		$data['Gallery']['foreign_key'] = $this->id;

		// check if GalleryImage data is in data 
		if (isset($data['GalleryImage'])){
			if ($data['GalleryImage']['filename']['error'] == 0 
					&& $this->Gallery->GalleryImage->add($data, 'filename')) {
				return true;
			} else {
				return false;
			}
		} 
	}
	
	
/**
 * Set a task as complete
 *
 * return bool
 */
	public function complete($data) {		
		if($this->saveAll($this->_isParentComplete($data, 'complete'))) {
			return true;
		} else {
			throw new Exception(__d('tasks', 'Task could not be marked complete.', true));
		}
	}
	
/**
 * Set parent task list completion status
 *
 * @return array
 */
	protected function _isParentComplete($data = null, $status = 'incomplete') {
		if (!empty($data['Task']['id'])) {
			$task = $this->find('first', array(
				'conditions' => array(
					'Task.id' => $data['Task']['id'],
					), 
				'contain' => 'ParentTask',
				));
			if (!empty($task['ParentTask']['id'])) {
				$incompleteChildren = $this->find('count', array(
					'conditions' => array(
						'Task.parent_id' => $task['ParentTask']['id'], 
						'Task.is_completed' => 0,
						'Task.id !=' => $task['Task']['id'], 
						),
					));
				// if all children are complete mark parent as complete
				if (!empty($incompleteChildren)) {
					$task['ParentTask']['is_completed'] = 0;
					$task['ParentTask']['completed_date'] = null;
				} else if ($status == 'complete') {
					$task['ParentTask']['is_completed'] = 1;
					$task['ParentTask']['completed_date'] = date('Y-m-d h:i:s');
				} else {
					$task['ParentTask']['is_completed'] = 0;
					$task['ParentTask']['completed_date'] = null;
				}
				$data = $task;
			}
		}
		
		$data['Task']['is_completed'] = $status == 'complete' ? 1 : 0;
		$data['Task']['completed_date'] = $status == 'complete' ? date('Y-m-d h:i:s') : null;
		return $data;
	}
	
	
/**
 * Mark a task as incomplete
 *
 * @return array
 */
	public function incomplete($data) {
		if($this->saveAll($this->_isParentComplete($data, 'incomplete'))) {
			return true;
		} else {
			throw new Exception(__d('tasks', 'Task could not be marked incomplete.', true));
		}
	}
	
	
/**
 * Standardize data
 *
 * @return array
 */
	public function cleanData($data) {
		if (empty($data['Task']['name']) && !empty($data['Task']['description'])) {
			$data['Task']['name'] = $data['Task']['description'];
		}		
		return $data;
	}
	
	
/**
 * Return a task for the view method
 * 
 * @return array
 */
	public function view($id = null, $params = null) {
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
			$plugin = ZuhaInflector::pluginize($task['Task']['model']);
			$model = $task['Task']['model'];
			$init = !empty($plugin) ? $plugin . '.' . $model : $model;
			$foreignKey = $task['Task']['foreign_key'];
			$result = ClassRegistry::init($init)->find('first', array('conditions' => array($model.'.id' => $foreignKey)));
			
			$task['Associated'] = $result;
		}
		return $task;
	}
	
	
/**
 * This trims an object, formats it's values if you need to, and returns the data to be merged with the Transaction data.
 * It is a required function for models that will be for sale via the Transactions Plugin.
 * @param string $key
 * @return array The necessary fields to add a Transaction Item
 */
	public function mapTransactionItem($key) {
	    
	    $itemData = $this->find('first', array('conditions' => array('id' => $key)));
	    
	    $fieldsToCopyDirectly = array();
	    
	    foreach($itemData['Task'] as $k => $v) {
		if(in_array($k, $fieldsToCopyDirectly)) {
		    $return['TransactionItem'][$k] = $v;
		}
	    }

	    // some custom field transformation action !
	    $assignee = $this->Assignee->find('first', array(
		'conditions' => array(
		    'id' => $itemData['Task']['assignee_id']
		),
		'fields' => array('full_name')
	    ));
	    $name = $assignee['Assignee']['full_name'] . ' : ' . $itemData['Task']['name'];
	    	    
	    $return['TransactionItem']['name'] = $name;

	    return $return;
	}
	
}