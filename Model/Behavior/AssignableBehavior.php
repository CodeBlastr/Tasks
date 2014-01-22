<?php
App::uses('User', 'Users.Model');
App::uses('Task', 'Tasks.Model');
	
			
/**
 * $this->hasMany['Task'] = array(
	'className' => 'Tasks.Task',
	'foreignKey' => 'foreign_key',
	'dependent' => true,
	'conditions' => array('Task.model' => 'Contact'),
	'fields' => '',
	'order' => '',
	'limit' => '',
	'offset' => '',
	'exclusive' => '',
	'finderQuery' => '',
	'counterQuery' => ''
 * @todo Update Contacts plugin, and Projects Plugin to use this behavior instead of the custom coded versions.
);*/
class AssignableBehavior extends ModelBehavior {

	public $settings = array(
		'notifyAssignee' => false,
		'notifySubject' => 'New Task',
		'notifyMessage' => 'New task assigned.',
		'dueDate' => ''
		);

	public $assigneeChanged = false;
	
	public $taskId = null;
/**
 * Setup 
 * 
 * @param Model $Model
 * @param array $settings
 */
	public function setup(Model $Model, $settings = array()) {
		$this->settings['dueDate'] = date('Y-m-d'); // default to today
		$this->settings = array_merge($this->settings, $settings);
		$this->User = new User;
		$this->Task = new Task;
	}
	
	
/**
 * Before Save Callback
 * 
 * Get whether the assignee is changing for use in afterSave()
 */
 	public function beforeSave(Model $Model) {
 		$this->checkAssigneeChange($Model);
		return true;
 	}
	
/**
 * After Save Callback
 * 
 * By default, adding the Loggable behavior will log when a record is created only.
 * 
 * @param Model $Model
 * @param bool $created
 */
	public function afterSave(Model $Model, $created) {
		return $this->triggerAssignment($Model);
	}
    
/**
 * Trigger Assignment method
 * 
 * @param Model $Model
 * @param array $settings
 */
	public function triggerAssignment(Model $Model, $settings = array()) {
		$settings = !empty($settings) ? array_merge($this->settings, $settings) : $this->settings;
		if (!empty($Model->data[$Model->alias]['assignee_id'])) {
			if ($this->assigneeChanged === true) {
		        $data['Task'] = !empty($Model->data['Task']) ? $Model->data['Task'] : null; // give us any task data submitted
		        $data['Task']['id'] = !empty($data['Task']['id']) ? $data['Task']['id'] : $this->taskId;
				$data['Task']['name'] = !empty($data['Task']['name']) ? $data['Task']['name'] : $Model->alias . ' Assigned';
		        $data['Task']['description'] = !empty($data['Task']['description']) ? $data['Task']['description'] : null;
		        $data['Task']['model'] = !empty($data['Task']['model']) ? $data['Task']['mode'] : $Model->alias;
		        $data['Task']['foreign_key'] = !empty($data['Task']['foreign_key']) ? $data['Task']['foreign_key'] : $Model->id;
		        $data['Task']['assignee_id'] = !empty($data['Task']['assignee_id']) ? $data['Task']['assignee_id'] : $Model->data[$Model->alias]['assignee_id'];
		        $data['Task']['due_date'] = !empty($data['Task']['due_date']) ? $data['Task']['due_date'] : $settings['dueDate'];
				
				if ($this->Task->save($data)) {
					if ($this->notifyAssignee($Model)) {
						return true;
					}
 					return true;
				} else {
					throw new Exception(__('Assignment failed'));
				}
			}
		}
		return true;
	}
 
 
/**
 * Notify Assignee Method
 * 
 * sends an email to the assignee
 * 
 * @return void
 */
 	public function notifyAssignee(Model $Model) {
		if (!empty($Model->data[$Model->alias]['assignee_id']) && !empty($this->settings['notifyAssignee']) && $this->assigneeChanged === true) {
			$this->User->id = $Model->data[$Model->alias]['assignee_id'];
			$assignee = $this->User->read();
			$recipient = $assignee['User']['email'];
			$subject = $this->settings['notifySubject']; 
			$message = $this->settings['notifyMessage'];
			//$message = 'Congratulations, you have received a new business opportunity.  <br /><br /> View ' . $this->data['Contact']['contact_type'] . ' <a href="http://' . $_SERVER['HTTP_HOST'] . '/contacts/contacts/view/' . $this->data['Contact']['id'] .'">' . $this->data['Contact']['name'] .'</a> here.  Where you can track activity, change the status, create an estimate, or set a reminder to follow up.'; 
			if ($Model->__sendMail($recipient, $subject, $message)) {
				return true;
			} else {
				exit;
				throw new Exception(__('Assignee could not be notified.'));
			}
		}
		return true;
 	}


/**
 * Check Assignee Change Method
 * 
 * @param
 * @return void
 */
 	public function checkAssigneeChange(Model $Model) {
		if (!empty($Model->data[$Model->alias]['assignee_id'])) {
			if (!empty($Model->data[$Model->alias]['id'])) {
				// check to see if assignee has been updated
				$result = $Model->find('first', array(
					'conditions' => array(
						$Model->alias.'.id' => $Model->data[$Model->alias]['id'],
						$Model->alias.'.assignee_id' => $Model->data[$Model->alias]['assignee_id']
						)
					));
				if (empty($result)) {
					// assignee has changed
					$this->taskId = $result['Transaction']['id']; 
					$this->assigneeChanged = true;
				}
			} else {
				// if the contact id is empty this is a new record
				$this->assigneeChanged = true;
			}
		}
 	}

}