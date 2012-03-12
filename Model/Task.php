<?php
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
	
	/*function __construct($id = false, $table = null, $ds = null) {
    	parent::__construct($id, $table, $ds);
	    $this->virtualFields['displayName'] = sprintf('CONCAT(%s.name, " ", %s.description)', $this->alias, $this->alias);
		$this->displayField = 'displayName';
    }*/
	
	public function add($data) {
		$data = $this->cleanData($data);
		
		if ($this->save($data)) :
			$this->galleryData($data);
			return true;
		else : 
			return false;
		endif;
	}
	
	/*
	 * galleryData saves the gallery if there is GalleryImage data present 
	 * return True/False
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
	
	public function complete($data) {
		$data['Task']['is_completed'] = 1;
		$data['Task']['completed_date'] = date('Y-m-d h:i:s');
		if($this->save($data)) : 
			return true;
		else :
			return false;
		endif;
	}
	
	
	public function incomplete($data) {
		$data['Task']['is_completed'] = 0;
		if($this->save($data)) : 
			return true;
		else :
			return false;
		endif;
	}
	
	public function cleanData($data) {
		if (empty($data['Task']['name']) && !empty($data['Task']['description'])) :
			$data['Task']['name'] = $data['Task']['description'];
		endif;
		
		return $data;
	}
	
	
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
}
?>