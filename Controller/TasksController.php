<?php
class AppTasksController extends TasksAppController { 

	public $name = 'Tasks';

	public $uses = array(
		'Tasks.Task',
		//'Tasks.TaskAttachment', 
	);

	public $allowedActions = array('desktop_index', 'desktop_view');

	public $Text; // WHAT THE HELL IS THIS, PUT A COMMENT ON IT!!!
	
	public function __construct($request = null, $response = null) {
		parent::__construct($request, $response);
		if ( in_array('Comments', CakePlugin::loaded()) ) {
			$this->components['Comments.Comments'] = array('userModelClass' => 'Users.User');
		}
	}

/**
 * Before filter callback
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->passedArgs['comment_view_type'] = 'flat';
	}
	
/**
 * Gadget method 
 * Used for gmail gadget view 
 * 
 * @todo delete this
 */
	public function gadget(){
		$this->layout = 'gadget';
		
		$contactTypes = Zuha::enum('CONTACTTYPE');
		$contactSources = Zuha::enum('CONTACTSOURCE');
		$contactIndustries = Zuha::enum('CONTACTINDUSTRY');
		$contactRatings = Zuha::enum('CONTACTRATING');
		$contactDetailTypes = Zuha::enum('CONTACTDETAIL');
		
		$parents = $this->Task->ParentTask->find('list');
		$assignees = $this->Task->Assignee->find('list');
		$this->set(compact('parents','assignees'));
		$this->set(compact('contactDetailTypes', 'contactTypes', 'contactSources', 'contactIndustries', 'contactRatings'));
	}
	
/**
 * Index method
 */
	public function index() {
		$this->helpers[] = 'Calendar';
		$this->paginate['conditions']['Task.parent_id'] = null;
		$this->paginate['conditions']['Task.is_completed'] = 0;
		$this->paginate['order']['Task.order'] = 'ASC';
		$this->paginate['order']['Task.due_date'] = 'ASC';
		$this->paginate['contain'][] = 'Assignee';
		$this->paginate['contain']['ChildTask'][] = 'Assignee';
		$this->set('tasks', $this->request->data = $this->paginate());
		$this->set('page_title_for_layout', 'All Task Lists');
		$this->set('title_for_layout', 'All Task Lists');
		return $this->request->data;
	}

/**
 * View method
 */
	public function view($id = null) {
		try {
			$task = $this->Task->view($id);
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect(array('action' => 'index'));
		}

		if (!empty($task['Task']['parent_id'])) { 
			$this->_single($task);
		} else {
			$this->set('task', $task);

			$this->set('childTasks', $this->_pendingChildTasks($task['Task']['id']));
			$this->set('finishedChildTasks', $this->_completedChildTasks($task['Task']['id']));
			$this->set('assignees', $this->Task->Assignee->find('list'));
			
			$this->set('parentId', $id);
			$this->set('showGallery', true);
			$this->set('galleryModel', array('name' => 'Task', 'alias'=>'Task'));
			$this->set('galleryForeignKey', 'id');
			$this->set('model', $task['Task']['model']);
			$this->set('foreignKey', $task['Task']['foreign_key']);
			$this->set('modelName', 'Task');
			$this->set('pluginName', 'tasks');
			$this->set('displayName', 'name');
			$this->set('displayDescription', 'description');
			$this->set('title_for_layout', __('%s - %s', $task['Task']['name'], $task['Associated'][$task['Task']['model']]['name']));
			$this->set('page_title_for_layout', __('%s <small>%s</small>', $task['Task']['name'], $task['Associated'][$task['Task']['model']]['name']));
		}		
	}

/**
 * Pending Child Tasks
 */
	protected function _pendingChildTasks($parentTaskId) {
		unset($this->paginate);
		$this->paginate = array(
			'conditions' => array(
				'Task.parent_id' => $parentTaskId,
				'Task.is_completed' => 0,
				),
			'contain' => array(
				'Assignee' => array(
					'fields' => array(
						'id',
						'full_name',
						),
					),
				),
			'fields' => array(
				'Task.id',
				'Task.due_date',
				'Task.assignee_id',
				'Task.name',
				'Task.description',
				),
			'order' => array(
				'Task.order',
				'Task.due_date',
				),
			);
		return $this->paginate('Task');
	}

/**
 * Completed Child Tasks
 */
	protected function _completedChildTasks($parentTaskId) {
		unset($this->paginate);
		$this->paginate = array(
			'conditions' => array(
				'Task.parent_id' => $parentTaskId,
				'Task.is_completed' => 1,
				),
			'contain' => array(
				'Assignee' => array(
					'fields' => array(
						'id',
						'full_name',
						),
					),
				),
			'fields' => array(
				'Task.id',
				'Task.due_date',
				'Task.completed_date',
				'Task.assignee_id',
				'Task.name',
				'Task.description',
				),
			'order' => array(
				'Task.order',
				'Task.due_date',
				),
			);
		return $this->paginate('Task');
	}


/** 
 * Display a single task instead of the task list like the view does.
 */
	protected function _single($task) {
		$Model = ClassRegistry::init('Projects.Project');
		$this->set('task', $task);
		$this->set('model', $task['Task']['model']);
		$this->set('foreignKey', $task['Task']['foreign_key']);
		$this->set('modelDisplayField', $Model->displayField);
		$this->set('page_title_for_layout', 'Tasks for '.$task['Associated'][$task['Task']['model']][$Model->displayField]);
		$this->render('single');
	}


/**
 * Add method
 * 
 * @todo	We need to support multiple people being assigned.  That might be the with usable behavior, but need some more thought put into it. 
 */
	public function add($model = null, $foreignKey = null, $id = null) {
		if (!empty($this->request->data)) {
			// find the users from the habtm users array
			if (!empty($this->request->data['Task']['assignee_id'])) {
				$recipients = $this->Task->Assignee->find('all', array(
					'conditions' => array(
						'Assignee.id' => $this->request->data['Task']['assignee_id'],
						),
					));
			}
			if ($this->Task->add($this->request->data)) {
				// send the message via email
				if (!empty($recipients)) {
					foreach ($recipients as $recipient) {
						$message = $this->request->data['Task']['name'];
						$message .= '<p>You can view and comment on this this task here: <a href="'.$_SERVER['HTTP_REFERER'].'">'.$_SERVER['HTTP_REFERER'].'</a></p>';
						$this->__sendMail($recipient['Assignee']['email'], 'Task Assigned', $message, $template = 'default');
						$this->Session->setFlash(__('The Task has been saved', true));
					}
				} else {
					$this->Session->setFlash(__('The Task List has been saved', true));
				}
				// go to parent if this was a sub item added
				$id = !empty($this->request->data['Task']['parent_id']) ? $this->request->data['Task']['parent_id'] : $this->Task->id;
				$this->redirect(array('action' => 'view', $id));
			} else {
				$this->Session->setFlash(__('The Task could not be saved. Please, try again.'));
			}
		}
		if (!empty($model) && !empty($foreignKey) && !empty($id)) {
			$this->request->data['Task']['parent_id'] = $id;
			$this->request->data['Task']['model'] = $model;
			$this->request->data['Task']['foreign_key'] = $foreignKey;
		} elseif (!empty($model) && empty($foreignKey)) {
			// if the model finds a task then this is a child we're creating
			$task = $this->Task->read(null, $model);
			$this->request->data['Task']['parent_id'] = !empty($task) ? $task['Task']['id'] : null;
		} else { 
			$this->request->data['Task']['model'] = !empty($model) ? $model : null;
			$this->request->data['Task']['foreign_key'] = !empty($foreignKey) ? $foreignKey : null;
		}
		
		$parents = $this->Task->ParentTask->find('list');
		$assignees = $this->Task->Assignee->find('list');
		$this->set(compact('parents','assignees'));
	}

/**
 * Edit method
 * 
 * @param string uuid
 */
	public function edit($id = null) {
		if (!empty($this->request->data)) {
			if ($this->Task->add($this->request->data)) {
				$this->Session->setFlash(__('The Task has been saved'));
				// go to parent if this is a child
				$id = !empty($this->request->data['Task']['parent_id']) ? $this->request->data['Task']['parent_id'] : $this->Task->id;
				$this->redirect(array('action' => 'view', $id));
			} else {
				$this->Session->setFlash(__('The Task could not be saved. Please, try again.'));
			}
		}
		if (empty($this->request->data)) {
			$this->request->data = $this->Task->read(null, $id);
		}
		$parents = $this->Task->ParentTask->find('list', array(
			'conditions' => array(
				'ParentTask.model' => $this->request->data['Task']['model'],
				'ParentTask.foreign_key' => $this->request->data['Task']['foreign_key'],
				'ParentTask.parent_id' => null,
				),
			));
		$assignees = $this->Task->Assignee->find('list');
		$this->set(compact('parents','assignees'));
	}

/**
 * Delete method
 * 
 * @param uuid $id
 */
	public function delete($id = null) {
		$this->Task->id = $id;
		if (!$this->Task->exists()) {
			throw new NotFoundException(__('Invalid task'));
		}
		if ($this->Task->delete($id)) {
			$this->Session->setFlash(__('Deleted'));
			$this->redirect($this->referer());
		} else {
			$this->Session->setFlash(__('Error deleting, please try again.'));
			$this->redirect($this->referer());
		}
	}
	
/**
 * Need to make it so that we set the related model as belongsTo and then pull the display field and add it onto the "name/description".
 * 
 * @todo move this related stuff to the model, so that its always added to the task views (OR EVEN BEETER, MAKE IT RELATABLE to see if it works, using the RelatableBehavior)
 */
	public function my() {
		if (empty($this->request->params['named']['filter'])) {
			$this->redirect(array('filter' => 'completed:0'));
		}
		// $this->paginate['conditions']['Task.assignee_id'] = $this->Session->read('Auth.User.id');
		// $this->paginate['order']['Task.order'] = 'asc';
		// $this->paginate['order']['Task.due_date'] = 'asc';
		$this->index();
		$tasks = $this->paginate('Task');
		
		// THIS DOES NOT BELONG HERE 
		// There is a function called origin_afterFind($results, $primary) that you can put in the original plugin's model
		// to change the data before you out put it.  This changing of the name by default is not good.  12/7/2013 RK 
		// $rawtasks = $this->paginate('Task');
		
		// start the related model functions
		// $related = Set::combine($rawtasks, '{n}.Task.id', '{n}.Task.foreign_key', '{n}.Task.model');
		// foreach($related as $model => $foreignKeys) {
			// if (!empty($model)) {
				// App::uses($model, ZuhaInflector::pluginize($model). '.Model');
				// $Related = new $model;
				// $displayField = $Related->displayField;
				// $associated = $Related->find('all', array(
					// 'conditions' => array(
						// "{$model}.id" => $foreignKeys, 
						// ),
					// 'fields' => array(
						// 'id',
						// $displayField,
						// ),
					// ));
				// $assoc[$model] = Set::combine($associated, "{n}.{$model}.id", "{n}.{$model}.{$displayField}");
			// }
		// }
		// $i = 0;
		// foreach ($rawtasks as $task) {
			// $tasks[$i] = $task;
			// if(!empty($assoc[$task['Task']['model']][$task['Task']['foreign_key']])) {
				// $tasks[$i]['Task']['name'] = $task['Task']['name'] . ' <span class="taskAssociate">' . $assoc[$task['Task']['model']][$task['Task']['foreign_key']] . '</span>';
			// }
			// $i++;
		// }
		// end the related model functions
		$this->set(compact('tasks'));
		$this->set('page_title_for_layout', 'My Tasks');
		$this->set('title_for_layout', 'My Tasks');
	}

/**
 * My Lists
 * Used to get list of parent tasks of logged in user
 */
	public function my_lists() {
		if (empty($this->request->params['named']['filter'])) {
			$this->redirect(array('filter' => 'completed:0'));
		}
		$this->paginate['conditions']['Task.assignee_id'] = $this->Session->read('Auth.User.id');
		$this->paginate['conditions']['Task.parent_id'] = null;
		$this->paginate['order']['Task.order'] = 'asc';
		$this->paginate['order']['Task.due_date'] = 'asc';
		$rawtasks = $this->paginate('Task');
		// start the related model functions
		$related = Set::combine($rawtasks, '{n}.Task.id', '{n}.Task.foreign_key', '{n}.Task.model');
		foreach($related as $model => $foreignKeys) {
			if (!empty($model)) {
				App::uses($model, ZuhaInflector::pluginize($model). '.Model');
				$Related = new $model;
				$displayField = $Related->displayField;
				$associated = $Related->find('all', array(
					'conditions' => array(
						"{$model}.id" => $foreignKeys, 
						),
					'fields' => array(
						'id',
						$displayField,
						),
					));
				$assoc[$model] = Set::combine($associated, "{n}.{$model}.id", "{n}.{$model}.{$displayField}");
			}
		}
		$i = 0;
		foreach ($rawtasks as $task) {
			$tasks[$i] = $task;
			if(!empty($assoc[$task['Task']['model']][$task['Task']['foreign_key']])) {
				$tasks[$i]['Task']['name'] = $task['Task']['name'] . ' <span class="taskAssociate">' . $assoc[$task['Task']['model']][$task['Task']['foreign_key']] . '</span>';
			}
			$i++;
		}
		// end the related model functions
		$this->set(compact('tasks'));
		$this->set('page_title_for_layout', 'My '.($this->request->params['named']['filter']['completed'] == 1 ? 'Completed' : 'Incomplete').' Tasks');
		$this->render('my');
	}

/**
 * Sort Order
 */
	public function sort_order() {
		$taskOrders = explode(',', $_POST['taskOrder']);
		foreach ($taskOrders as $key => $value) {
			$this->request->data['Task'] = array('id' => $value, 'order' => $key+1);
			if($this->Task->add($this->request->data)) {
				$this->set(compact('taskOrders'));
			} else {
				$this->Session->setFlash('There was an error updating Task.');
			}
		}
	}
	

/**
 * Mark a task as complete
 *
 * @todo Move the email function to the model (we can send email from models now), and get exceptions if it doesn't. 
 */ 
	public function complete($id = null) {
		if(!empty($id)) {
			$data['Task']['id'] = $id;
			try {
				$this->Task->complete($data);
				
				// 
				$task = $this->Task->find('first', array('recursive' => 0, 'conditions'=>array('Task.id'=>$id), 'fields'=>array('Task.id', 'Task.due_date', 'Task.assignee_id', 'Task.name', 'Task.description', 'Task.model', 'Task.foreign_key', 'Creator.id', 'Creator.email', 'Creator.full_name', 'Assignee.email')));
				if(!empty($task)) {
					$subject = 'A task "'.$task['Task']['name'].'" was marked as completed';				
					$message = '<p>The following task was marked as completed</p>';
					
					$taskLabel = $task['Task']['name'];

					$associated = $this->__findAssociated("Task", $task);
					
					if($associated)	{
						$taskLabel .= ' : ' . $associated[$task['Task']['model']]['displayName'];	
					}
					
					$message .= '<p><a href="'. Router::url(array('controller'=>'tasks', 'plugin'=>'tasks', 'action'=>'view', $task['Task']['id']), true) . '">' . $taskLabel . '</a> : Due on '.date('m/d/Y', strtotime($task['Task']['due_date']));			
					$message .= '</p>';
					$recepients = array_filter(array($task['Creator']['email'], $task['Assignee']['email']), 'strlen');
					//$recepients = 'arvind.mailto@gmail.com';
					$this->__sendMail($recepients, $subject, $message, $template = 'default');
					# send the message via email
				}
				$this->Session->setFlash('Task Completed');
				$this->redirect($this->referer());
			} catch (Exception $e) {
				$this->Session->setFlash($e->getMessage());
				$this->redirect($this->referer());
			}
		} else {
			$this->Session->setFlash('Invalid task.');
			$this->redirect($this->referer());
		}
	}
	

/**
 * Mark a task as incomplete
 *
 */ 	
	public function incomplete($id = null) {
		if(!empty($id)) {
			$data['Task']['id'] = $id;
			if($this->Task->incomplete($data)) {
				$this->Session->setFlash('Task Incomplete');
				$this->redirect($this->referer());
			} else {
				$this->Session->setFlash('There was an error updating Task.');
				$this->redirect($this->referer());
			}
		} else {
			$this->Session->setFlash('Invalid task.');
			$this->redirect($this->referer(), 'error');
		}			
	}
	
	
/** 
 * show drop downs for the selected projects
 */
    public function desktop_index($id = null, $userId = null){
		$Project = ClassRegistry::init('Projects.Project');
		$projects = $Project->findUsedObjects($userId, 'all', array('contain' => array('Contact'), 'nocheck' => $userId));
		foreach ($projects as $project) {
			$managedProjects[] = $project['Project']['id'];
		}
		
		#find the tasks assigned to
    	$taskList =  $this->Task->find('all', array(
			'conditions' => array(
				'Task.foreign_key' => $id, 
				'Task.model' => 'Project', 
				'Task.parent_id is not null', 
				'Task.is_completed' => 0, 
				'Task.assignee_id' => $userId,
				)
			));
		$str = '<option value="">-- Select --</option>';
        for($i= 0 ;$i<sizeof($taskList);$i++){
            $str .= "<option value=".$taskList[$i]['Task']['id'].">".substr($taskList[$i]['Task']['name'], 0, 40).'...'."</option>";
        }
		$this->set('data', $str);
		$this->layout = false;
    }
	
	
/** 
 * Find the estimated hours and tracked hours
 * @todo 	This is deprecated, and needs to be moved to Tasks
 */
    public function desktop_view($id = null){
		$allocatedHours = $this->Task->find('first', array('conditions' => array('id' => $id), 'fields' => 'due_date'));
		if (!empty($allocatedHours['Task']['estimated_hours'])) {
			$allocatedHoursSum = $allocatedHours['Task']['estimated_hours'];
		} else {
			$allocatedHoursSum = '0.00';
		}
		# find the number of hours that have been logged for this task
		$TimesheetTime = ClassRegistry::init('Timesheets.TimesheetTime');
		$trackedTimes = $TimesheetTime->find('all', array('conditions' => array('task_id' => $id), 'fields' => 'hours'));
		if(!empty($trackedTimes)) {
			foreach ($trackedTimes as $trackedTime) {
				$trackedHours[] = $trackedTime['TimesheetTime']['hours'];
			}
			$trackedHoursSum = array_sum($trackedHours);
		} else {
			$trackedHoursSum = '0.00';
		}
		# this is what it should be to be mvc compliant
		$this->set('data', array($allocatedHoursSum, $trackedHoursSum, $allocatedHours['Task']['due_date']));
		$this->layout = false;
		
    }
    
/** 
 * Run cron job, called through Task/tasks_callback.php, runcron() method found in appController so it could be run from anywhere. for example http://razorit.com/webpages/webpages/runcron and http://razorit.com/users/users/runcron.
 * @param - $assignee_id (int), for basically for testing purpose. If a valid assignee id is passed task notifications are sent to that particular user only.
 */
    public function __cron($options=array()) {
    	ClassRegistry::init("Tasks.Task");
    	
    	//$this->Task->query("updated tasks set last_notified_date=null");exit;    	
    	
    	/*$options['skip_digest']=false;
    	$options['skip_single']=false;
    	
    	if(isset($options['tn_skip']))	{
    		$skipped = explode(',', $options['tn_skip']);
    		
    		if(in_array('digest', $skipped))	{
    			$options['skip_digest']=true;
    		}
    		
    		if(in_array('single', $skipped))	{
    			$options['skip_single']=true;
    		}
    	}*/
    	
    	if(isset($options['assignee_id']) && !(int)$options['assignee_id'])	{
    		unset($options['assignee_id']);
    	}
    	
    	$options['tn_skip_update'] = false;
    	
        


    	//if(!$options['skip_single']) {
    		
    		/*if(!$options['skip_digest']))	{
    			$options['tn_skip_update'] = true;
    		}*/		
    		
    		$this->_overdue_notify($options);
    		
    		//$options['tn_skip_update'] = false;
    		
    		//after the overdue single notifications has been send make it to repeat for same day for the Digest
    		//if(!isset($options['skip_digest']))	{
    		//	$options['tn_repeatx'] = true;
    		//}
       	//}
     	
        //if(!isset($options['skip_digest'])) {        	
        	$this->_daily_digest($options);
        //}
        
        echo $this->NotificatonMsg;
    }

/** 
 * send email notifications for incomplete and overdue tasks
 */
    protected function _daily_digest($options=array()) {
    	
    	App::import('Helper', 'Text');
		App::uses('View', 'View');
		$this->Text = new TextHelper(new View($this)); // removed the & before 'new' ^JB
   	
        $this->autoRender=false;
        $this->Task->recursive = 0;

        if(!isset($options['tn_repeat']))	{
        	$conditions['AND'] = array(
        		'OR'=>array(
					'Task.last_notified_date'=>null,
					'Task.last_notified_date <>'=>date('Y-m-d')
				)
			);
        }	else	{
        	/*$conditions['AND'] = array(
        		'OR'=>array(
					array('Task.last_notified_date'=>null),
					array('Task.last_notified_date'=>date('Y-m-d'))
				)
			);*/
         }
        
        $conditions['Task.assignee_id <>'] = null;
        $conditions['Task.due_date <>'] = '0000-00-00';
        $conditions['OR'] = array(
			array('Task.is_completed' => 0),
			array('Task.is_completed' => null),
		);

        $allAssignees = $this->Task->find('all', array(
        	'conditions'=>$conditions, 
        	'group'=>'Task.assignee_id', 
        	'fields'=>array('Task.assignee_id'), 
        	'recursive'=>0
        	
        	));
        
        unset($conditions['Task.assignee_id <>']);  
        
        $creatorMessages = array();

        foreach($allAssignees as $assignee) {
        	
        	$creators = array();

            $assigneeDetails = $this->Task->Assignee->find('first', array('conditions'=>array('Assignee.id'=>$assignee['Task']['assignee_id']), 'fields'=>array('id', 'full_name', 'email')));
            $conditions['Task.assignee_id'] = $assignee['Task']['assignee_id'];            
            $assigneeTasks = $this->Task->find('all', array('conditions'=>$conditions, 'fields'=>array('Task.id', 'Task.due_date', 'Task.assignee_id', 'Task.name', 'Task.description', 'Creator.id', 'Creator.email', 'Creator.full_name', 'Task.model', 'Task.foreign_key'), 'order'=>array('Task.due_date ASC')));     
            
            if(isset($options['assignee_id']) && $assignee['Task']['assignee_id']!=$options['assignee_id']) continue;
            
            $digestMessage = '';
            
            $msgArray = array(
            		'Over Due'=>null,
            		'Due Today'=>null,
            		'Due This Week'=>null,
            		'Coming Soon'=>null
            	);
            
            foreach($assigneeTasks as $task)    {
            	
            	$associated = $this->__findAssociated("Task", $task);
            	
            	$associated = ($associated) ? $associated : array();
            	
            	$creators[$task['Creator']['id']] = array('full_name'=>$task['Creator']['full_name'], 'email'=>$task['Creator']['email']);

            	$eachMessage = array_merge($task, $associated);
            	
                if(strtotime($task['Task']['due_date']) < strtotime(date('Y-m-d')))    {                	
                	$msgArray['Over Due'][] = $eachMessage;
                	//$creatorMessages[$task['Creator']['id']]['Over Due'][] = $eachMessage;
                }	elseif(strtotime($task['Task']['due_date']) == strtotime(date('Y-m-d')))	{
                	$msgArray['Due Today'][] = $eachMessage;					
					//$creatorMessages[$task['Creator']['id']]['Due Today'][] = $eachMessage;					
                }	elseif (strtotime($task['Task']['due_date']) <= strtotime(date('Y-m-d') . ' add +7 day'))	{
                    $msgArray['Due This Week'][] = $eachMessage;					
					//$creatorMessages[$task['Creator']['id']]['Due This Week'][] = $eachMessage;
                }	else{
                	$msgArray['Coming Soon'][] = $eachMessage;					
					//$creatorMessages[$task['Creator']['id']]['Coming Soon'][] = $eachMessage;
                }
                
                //if($options['skip_single'])	{
					$this->Task->id = $task['Task']['id'];
					$this->Task->saveField('last_notified_date', date('Y-m-d'));
				//}
            }
            
            foreach($msgArray as $title=>$dueTasks)	{
            	if($dueTasks)	{
            		$digestMessage .= $title . "\n";
            		$digestMessage .= '<ul>' . "\n";
            		foreach($dueTasks as $dueTask)	{
            			$digestMessage .= '<li>';    			
            			$taskLabel = $dueTask['Task']['name'];            			
            			if(isset($dueTask['Project']))	{
            				$taskLabel .= ' : ' . $dueTask[$dueTask['Task']['model']]['displayName'];
            			}            			
						$digestMessage .= '<a href="'. Router::url(array('controller'=>'tasks', 'plugin'=>'tasks', 'action'=>'view', $dueTask['Task']['id']), true) . '">' . $taskLabel . '</a> : ' . date('M d, \'y', strtotime($dueTask['Task']['due_date'])) . "<br/ >\n";
						$digestMessage .= $this->Text->truncate($dueTask['Task']['description']);
						$digestMessage .= '</li>'."\n";
            		}
            		$digestMessage .= '</ul>' . "\n";
            	}
            }
            
            //debug($assigneeDetails['Assignee']['email']);            
            //if($assigneeDetails['Assignee']['email']!='php.arvind@gmail.com') continue;            
            $this->__sendMail($assigneeDetails['Assignee']['email'], $assigneeDetails['Assignee']['full_name'] . '\'s Daily Task Digest', $digestMessage, $template = 'default');
            
            foreach($creators as $creator)	{
            	$this->__sendMail($creator['email'], $assigneeDetails['Assignee']['full_name'] . '\'s Daily Task Digest', $digestMessage, $template = 'default');
            }
            
            $this->NotificatonMsg .= 'Subject: <strong>Daily Task Digest</strong>, To: <strong>'.$assigneeDetails['Assignee']['email'].'</strong>, Message: <br />'. $digestMessage . '<br /><hr><br />';
        }
        
        //creator's custom personal digest which contained tasks asssigned by creators only. commented
        
        /*
		foreach($creatorMessages as $creator_id=>$messages)	{
		
			$digestMessage = '';
			
			foreach($messages as $taskTitle=>$msgArray)	{
				$digestMessage .= $taskTitle . "\n";
				$digestMessage .= '<ul>' . "\n";
				foreach($msgArray as $dueTask)	{
					$digestMessage .= '<li>';      			
					$taskLabel = $dueTask['Task']['name'];            			
					if(isset($dueTask['Project']))	{
						$taskLabel .= ' : ' . $dueTask['Project']['displayName'];
					}            			
					$digestMessage .= '<a href="'. Router::url(array('controller'=>'tasks', 'plugin'=>'tasks', 'action'=>'view', $dueTask['Task']['id']), true) . '">' . $taskLabel . '</a> : ' . date('M d, \'y', strtotime($dueTask['Task']['due_date'])) . "<br/ >\n";
					$digestMessage .= $this->Text->truncate($dueTask['Task']['description']);
					$digestMessage .= '</li>'."\n";
				}
				$digestMessage .= '</ul>';
			}
			
			$subject = $creators[$creator_id]['full_name'] ."'s Task Digest for ".date("m/d/Y");
			
			if($digestMessage!="")	{
				
				 //if($creators[$creator_id]['email']!='php.arvind@gmail.com') continue;
				 
				$this->__sendMail($creators[$creator_id]['email'],  $subject, $digestMessage, $template = 'default');
				$this->NotificatonMsg .= 'Subject: <strong>'.$subject.'</strong>, To: <strong>'.$creators[$creator_id]['email'].'</strong>, Message: <br />'. $digestMessage . '<br /><hr><br />';
				break;
			}
		}*/
    }

/** 
 * send email notifications for overdue tasks
 */
    protected function _overdue_notify($options=array())   {

        $this->autoRender=false;
        $this->Task->recursive = 0;
        
        if(!isset($options['tn_repeat']))	{
        	$conditions['AND'] = array(
        		'OR'=>array(
					'Task.last_notified_date'=>null,
					'Task.last_notified_date <>'=>date('Y-m-d')
				)
			);
        }	else	{
        	/*$conditions['AND'] = array(
        		'OR'=>array(
					array('Task.last_notified_date'=>null),
					array('Task.last_notified_date'=>date('Y-m-d'))
				)
			);*/
         }
        
        $conditions['Task.assignee_id <>'] = null;
        $conditions['Task.due_date <>'] = '0000-00-00';
        $conditions['OR'] = array(
				array('Task.is_completed' => 0),
				array('Task.is_completed' => null),
			);

        $conditions['Task.due_date <='] = date('Y-m-d');
       
        $allTasks = $this->Task->find('all', array('conditions'=>$conditions, 'order'=>'Task.due_date asc', 'fields'=>array('Task.id', 'Task.due_date', 'Task.assignee_id', 'Task.name', 'Task.description', 'Task.model', 'Task.foreign_key', 'Creator.email', 'Assignee.full_name', 'Assignee.email')));
  
        foreach($allTasks as $task) {
        	
        	$associated = $this->__findAssociated("Task", $task);

            if(isset($options['assignee_id']) && $task['Task']['assignee_id']!=$options['assignee_id']) continue;
            
            $taskLabel = $task['Task']['name'];
            
            if($associated)	{
            	$taskLabel .= ' : ' . $associated[$task['Task']['model']]['displayName'];
            }

            $message = '<p>You have a task which is over due.  If you\'ve received this message in error please login to mark the task as complete.  <a href="'. Router::url(array('controller'=>'tasks', 'plugin'=>'tasks', 'action'=>'view', $task['Task']['id']), true) . '">' . $taskLabel . '</a>' . '</p>';
            
            $subject = 'Overdue Task : Was due on' .  date('m/d/Y', strtotime($task['Task']['due_date']));
            //array($task['Assignee']['email'], $task['Creator']['email'])
            
            //if($task['Assignee']['email']!='php.arvind@gmail.com') continue;

            $this->__sendMail(array($task['Assignee']['email'], $task['Creator']['email']) , $subject, $message, $template = 'default');
            
            $this->NotificatonMsg .= 'Subject: <strong>'.$subject.'</strong>, To: <strong>'.$task['Assignee']['email'].', '.$task['Creator']['email'].'</strong>, Message: <br />'. $message . '<br /><hr><br />';
           
            /*if($options['skip_digest'])	{ //if it's only run for this particular task update
				$this->Task->id = $task['Task']['id'];
				$this->Task->saveField('last_notified_date', date('Y-m-d'));
            }*/
        }
    }
	
/**
 * @todo	 This send message thing is used here, and in the messages controller itself.  I don't know where we could put it so that its usable between both.  (Probably would have to do some kind of added on, slow component thing).
 * @todo 	 The task messaging is for the entire task list.  It is not per task, like it should be.  But there is some thought that needs to be put in about who gets notifications for tasks.  Assignee, Assigner, and what if you want someone else in on it.  So the todo, is put in that thought and make it happen. 
 */
	public function _callback_commentsafterAdd($options) {	
		if ($this->request->params['action'] == 'view') {	
			$this->Task->recursive = 0;
			$task = $this->Task->find('first', array('conditions'=>array('Task.id'=>$options['modelId']), 'fields'=>array('Task.id', 'Task.due_date', 'Task.assignee_id', 'Task.name', 'Task.description', 'Task.model', 'Task.foreign_key', 'Creator.id', 'Creator.email', 'Creator.full_name', 'Assignee.email')));
			if(!empty($task)) {
				$subject = 'A comment on the task "'.$task['Task']['name'].'" was posted';
				$taskLabel = $task['Task']['name'];
				$associated = $this->__findAssociated("Task", $task);
				if($associated)	{
					$taskLabel .= ' : ' . $associated[$task['Task']['model']]['displayName'];	
				}
				$message = '<p><a href="'. Router::url(array('controller'=>'tasks', 'plugin'=>'tasks', 'action'=>'view', $task['Task']['id']), true) . '">' . $taskLabel . '</a> : Due on '.date('m/d/Y', strtotime($task['Task']['due_date'])).'<br />';			
				$message .= $options['data']['Comment']['title'] . ' : ' . $options['data']['Comment']['body'];
				$message .= '</p>';
				$this->__sendMail(array($task['Creator']['email'], $task['Assignee']['email']), $subject, $message, $template = 'default');
			}
		}	
	}
	
/**
 * To get "model" data for a task. Project in this case
 *
 */
	public function __findAssociated($curr_model, $data=array())	{
		$this->autoRender=false;
		if(!isset($data[$curr_model]['model']) || !isset($data[$curr_model]['foreign_key'])) return false;
		if(!$data[$curr_model]['model']) return false;
		$plugin = ZuhaInflector::pluginize($data[$curr_model]['model']);
		$model = $data[$curr_model]['model'];
		$init = !empty($plugin) ? $plugin . '.' . $model : $model;
		$foreignKey = $data[$curr_model]['foreign_key'];
		$Model = ClassRegistry::init($init);
		return $Model->find('first', array('conditions' => array($model.'.id' => $foreignKey), 'fields'=>array($Model->name  . '.' . $Model->displayField)));
	}
	
/**
 * Private method
 */
 	public function privatize($id = null) {
		try {
			$data['Used']['foreign_key'] = $id;
			$this->Task->privatize($data);
			$this->Session->setFlash('Privatized');
			$this->redirect($this->referer());
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect($this->referer());
		}
	}
	
/**
 * Public method
 */
 	public function publicize($id = null) {
		try {
			$data['Used']['foreign_key'] = $id;
			$this->Task->publicize($data);
			$this->Session->setFlash('Publicized');
			$this->redirect($this->referer());
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect($this->referer());
		}
	}
	
}

if (!isset($refuseInit)) {
		class TasksController extends AppTasksController {}
}