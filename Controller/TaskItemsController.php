<?php
class AppTaskItemsController extends TasksAppController { 

	public $name = 'TaskItems';

/**
 * @todo This should be TaskChild at some point.  More comments 
 * on that in the Task Model. 
 */
	public $uses = array(
		'Tasks.Task',
	);

/**
 * Edit method
 */
	public function edit($id = null) {
		if ($this->request->is('post')) {
			if ($this->Task->save($this->request->data)) {
				$this->Session->setFlash(__('The Task has been saved'));
				// redirect to the parent
				$id = !empty($this->request->data['Task']['parent_id']) ? $this->request->data['Task']['parent_id'] : $this->Task->field('parent_id', array('Task.id' => $this->Task->id));
				$this->redirect(array('controller' => 'tasks', 'action' => 'view', $id));
			} else {
				$this->Session->setFlash(__('The Task could not be saved. Please, try again.'));
			}
		}
	}
	
}

if (!isset($refuseInit)) {
		class TaskItemsController extends AppTaskItemsController {}
}