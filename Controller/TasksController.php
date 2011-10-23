<?php
class TasksController extends TasksAppController {

	var $name = 'Tasks';
	var $components = array('Comments.Comments' => array('userModelClass' => 'Users.User'));
	var $allowedActions = array('desktop_index', 'desktop_view', 'testthefun');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->passedArgs['comment_view_type'] = 'threaded';
	}
	
	/*
	 * Function Gadget 
	 * use for gmail gadget view 
	 */
	function gadget(){
		$this->layout = 'gadget';
		
		$contactTypes = enum('CONTACTTYPE');
		$contactSources = enum('CONTACTSOURCE');
		$contactIndustries = enum('CONTACTINDUSTRY');
		$contactRatings = enum('CONTACTRATING');
		$contactDetailTypes = enum('CONTACTDETAIL');
		
		$parents = $this->Task->ParentTask->find('list');
		$assignees = $this->Task->Assignee->find('list');
		$this->set(compact('parents','assignees'));
		$this->set(compact('contactDetailTypes', 'contactTypes', 'contactSources', 'contactIndustries', 'contactRatings'));
	}
	
	function index() {
		$this->Task->recursive = 0;	
		$this->paginate = array(
			'conditions' => array(
				'Task.parent_id NOT' => null,
				'Task.is_completed' => 0,
				),
			'order' => array(
				'Task.order',
				'Task.due_date',
				),
			);
		$this->set('tasks', $this->paginate());
		$this->set('page_title_for_layout', 'All Tasks');
	}

	function view($id = null) {		
		try {
			$task = $this->Task->view($id);
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect(array('action' => 'index'));
		}	
		
	
		if (!empty($task['Task']['parent_id'])) : 
			$this->_single($task);
		else : 
			$this->set('task', $task);
			$this->paginate = array(
				'conditions' => array(
					'ChildTask.parent_id' => $id,
					),
				'fields' => array(
					'id',
					'due_date',
					'assignee_id',
					'name',
					),
				'order' => array(
					'ChildTask.order',
					'ChildTask.due_date',
					),
				);
			$this->set('childTasks', $this->paginate('ChildTask'));
			$this->set('parentId', $id);
			$this->set('model', $task['Task']['model']);
			$this->set('foreignKey', $task['Task']['foreign_key']);
			$this->set('modelName', 'ChildTask');
			$this->set('pluginName', 'tasks');
			$this->set('displayName', 'name');
			$this->set('displayDescription', '');
		endif;		
	}
	
	/** 
	 * Display a single task instead of the task list like the view does.
	 */
	function _single($task) {
		$Model = ClassRegistry::init('Projects.Project');
		$this->set('task', $task);
		$this->set('model', $task['Task']['model']);
		$this->set('foreignKey', $task['Task']['foreign_key']);
		$this->set('modelDisplayField', $Model->displayField);
		$this->set('page_title_for_layout', 'Tasks for '.$task['Associated'][$task['Task']['model']][$Model->displayField]);
		$this->render('single');
	}


	/**
	 * @todo	We need to support multiple people being assigned.  That might be the with usable behavior, but need some more thought put into it. 
	 */
	function add($model = null, $foreignKey = null, $id = null) {
		if (!empty($this->request->data)) {
			# find the users from the habtm users array
			if (!empty($this->request->data['Task']['assignee_id'])) : 
				$recipients = $this->Task->Assignee->find('all', array(
					'conditions' => array(
						'Assignee.id' => $this->request->data['Task']['assignee_id'],
						),
					));
			endif;
			if ($this->Task->add($this->request->data)) {
				# send the message via email
				if (!empty($recipients)) : foreach ($recipients as $recipient) :
					$message = $this->request->data['Task']['name'];
					$message .= '<p>You can view and comment on this this task here: <a href="'.$_SERVER['HTTP_REFERER'].'">'.$_SERVER['HTTP_REFERER'].'</a></p>';
					$this->__sendMail($recipient['Assignee']['email'], 'Task Assigned', $message, $template = 'default');
					$this->Session->setFlash(__('The Task has been saved', true));
				endforeach; else :
					$this->Session->setFlash(__('The Task List has been saved', true));
				endif;
				$this->redirect(array('action' => 'my'), 'success');
			} else {
				$this->Session->setFlash(__('The Task could not be saved. Please, try again.', true), 'error');
			}
		}
		if (!empty($model) && !empty($foreignKey) && !empty($id)) :
			$this->request->data['Task']['parent_id'] = $id;
			$this->request->data['Task']['model'] = $model;
			$this->request->data['Task']['foreign_key'] = $foreignKey;
		elseif (!empty($model) && empty($foreignKey)) :
			# if the model finds a task then this is a child we're creating
			$task = $this->Task->read(null, $model);
			$this->request->data['Task']['parent_id'] = !empty($task) ? $task['Task']['id'] : null;
		else : 
			$this->request->data['Task']['model'] = !empty($model) ? $model : null;
			$this->request->data['Task']['foreign_key'] = !empty($foreignKey) ? $foreignKey : null;
		endif;
		$parents = $this->Task->ParentTask->find('list');
		$assignees = $this->Task->Assignee->find('list');
		$this->set(compact('parents','assignees'));
	}


	function edit($id = null) {
		if (!empty($this->request->data)) {
			if ($this->Task->add($this->request->data)) {
				$this->Session->setFlash(__('The Task has been saved', true));
				$this->redirect($this->referer(), 'success');
			} else {
				$this->Session->setFlash(__('The Task could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->request->data)) {
			$this->request->data = $this->Task->read(null, $id);
		}
		$parents = $this->Task->ParentTask->find('list');
		$assignees = $this->Task->Assignee->find('list');
		$this->set(compact('parents','assignees'));
	}

	function delete($id = null) {
		$this->__delete('Task', $id);
	}
	
	/**
	 * Need to make it so that we set the related model as belongsTo and then pull the display field and add it onto the "name/description".
	 */
	function my() {
		# declare variable in case of non-use
		if (!isset($this->request->params['named']['completed'])) { $this->request->params['named']['completed'] = ''; }
		
		$conditions['Task.assignee_id'] = $this->Session->read('Auth.User.id');	
		if (!empty($this->request->params['named']['completed']) && $this->request->params['named']['completed'] == 1){
			$conditions['Task.is_completed'] = 1;
		} else {
			$conditions['OR'] = array(
				array('Task.is_completed' => 0),
				array('Task.is_completed' => null),
			);
		}
		$this->paginate = array('conditions' => $conditions, 'order' => array('Task.order' => 'asc'));
		$this->set('tasks', $this->paginate('Task'));
		$this->set('page_title_for_layout', 'My '.($this->request->params['named']['completed'] == 1 ? 'Completed' : 'Incomplete').' Tasks');
	}
		
		
	function sort_order() {
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
	
	
	function complete($id = null) {
		if(!empty($id)) :
			$data['Task']['id'] = $id;
			if ($this->Task->complete($data)) :
				$task = $this->Task->find('first', array('recursive'=>0, 'conditions'=>array('Task.id'=>$id), 'fields'=>array('Task.id', 'Task.due_date', 'Task.assignee_id', 'Task.name', 'Task.description', 'Task.model', 'Task.foreign_key', 'Creator.id', 'Creator.email', 'Creator.full_name', 'Assignee.email')));
				if(!empty($task)) :
					$subject = 'A task "'.$task['Task']['name'].'" was marked as completed';				
					$message = '<p>The following task was marked as completed</p>';
					
					$taskLabel = $task['Task']['name'];

					$associated = $this->__findAssociated("Task", $task);
					
					if($associated)	{
						$taskLabel .= ' : ' . $associated[$task['Task']['model']]['displayName'];	
					}
					
					$message .= '<p><a href="'. Router::url(array('controller'=>'tasks', 'plugin'=>'tasks', 'action'=>'view', $task['Task']['id']), true) . '">' . $taskLabel . '</a> : Due on '.date('m/d/Y', strtotime($task['Task']['due_date']));			
					$message .= '</p>';
					$recepients = array($task['Creator']['email'], $task['Assignee']['email']);
					//$recepients = 'arvind.mailto@gmail.com';
					$this->__sendMail($recepients, $subject, $message, $template = 'default');
					# send the message via email
				endif;
				$this->Session->setFlash('Task Completed');
				$this->redirect($this->referer(), 'success');
			else :
				$this->Session->setFlash('There was an error updating Task.');
				$this->redirect($this->referer(), 'error');
			endif;
		else :
			$this->Session->setFlash('Invalid task.');
			$this->redirect($this->referer(), 'error');
		endif;
	}
	
	
	function incomplete($id = null) {
		if(!empty($id)) {
			$data['Task']['id'] = $id;
			if($this->Task->incomplete($data)) {
				$this->Session->setFlash('Task Incomplete');
				$this->redirect($this->referer(), 'success');
			} else {
				$this->Session->setFlash('There was an error updating Task.');
				$this->redirect($this->referer(), 'error');
			}
		} else {
			$this->Session->setFlash('Invalid task.');
			$this->redirect($this->referer(), 'error');
		}			
	}
	
	
	/** 
	 * show drop downs for the selected projects
	 */
    function desktop_index($id = null, $userId = null){
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
		#$this->set('data', $str);
		echo $str;
		$this->render(false);
    }
	
	
	/** 
	 * Find the estimated hours and tracked hours
	 * @todo 	This is deprecated, and needs to be moved to Tasks
	 */
    function desktop_view($id = null){
		$allocatedHours = $this->Task->find('first', array('conditions' => array('id' => $id), 'fields' => 'due_date'));
		if (!empty($allocatedHours['Task']['estimated_hours'])) {
			$allocatedHoursSum = $allocatedHours['Task']['estimated_hours'];
		} else {
			$allocatedHoursSum = '0.00';
		}
		# find the number of hours that have been logged for this task
		$this->TimesheetTime = ClassRegistry::init('TimesheetTime');
		$trackedTimes = $this->TimesheetTime->find('all', array('conditions' => array('task_id' => $id), 'fields' => 'hours'));
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
    }
    
    /** 
    * Run cron job, called through Task/tasks_callback.php, runcron() method found in appController so it could be run from anywhere. for example http://razorit.com/webpages/webpages/runcron and http://razorit.com/users/users/runcron.
    * @param - $assignee_id (int), for basically for testing purpose. If a valid assignee id is passed task notifications are sent to that particular user only.
	 */

    function __cron($assignee_id=null) {
    	ClassRegistry::init("Tasks.Task");
    	$this->overdue_notify();
        $this->daily_digest();
        echo "Run at " . date("d-m-Y h:i:s");
        return;
    }

	/** 
	 * send email notifications for incomplete and overdue tasks
	 */

    function daily_digest($assignee_id=null) {
    	
        $this->autoRender=false;
        $this->Task->recursive = 0;
        $conditions['AND'] = array('OR'=>array('Task.last_notified_date'=>null, 'Task.last_notified_date <>'=>date('Y-m-d')));
        $conditions['Task.assignee_id <>'] = null;
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
        Configure::write('debug', 2);
        
        $creators = array();$creatorMessages = array();

        foreach($allAssignees as $assignee) {

            $assigneeDetails = $this->Task->Assignee->find('first', array('conditions'=>array('Assignee.id'=>$assignee['Task']['assignee_id']), 'fields'=>array('id', 'full_name', 'email')));
            $conditions['Task.assignee_id'] = $assignee['Task']['assignee_id'];            
            $assigneeTasks = $this->Task->find('all', array('conditions'=>$conditions, 'fields'=>array('Task.id', 'Task.due_date', 'Task.assignee_id', 'Task.name', 'Task.description', 'Creator.id', 'Creator.email', 'Creator.full_name', 'Task.model', 'Task.foreign_key'), 'order'=>array('Task.due_date ASC')));            
            if($assignee_id && $assignee['Task']['assignee_id']!=$assignee_id) continue;
            
            $digestMessage = '';  
            
            $overDueMessages = $todaysMessages = $thisWeekMessages = $comingSoonMessages = '';
            
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
                	$creatorMessages[$task['Creator']['id']]['Over Due'][] = $eachMessage;
                }	elseif(strtotime($task['Task']['due_date']) == strtotime(date('Y-m-d')))	{
                	$msgArray['Due Today'][] = $eachMessage;					
					$creatorMessages[$task['Creator']['id']]['Due Today'][] = $eachMessage;					
                }	elseif (strtotime($task['Task']['due_date']) <= strtotime(date('Y-m-d') . ' add +7 day'))	{
                    $msgArray['Due This Week'][] = $eachMessage;					
					$creatorMessages[$task['Creator']['id']]['Due This Week'][] = $eachMessage;
                }	else{
                	$msgArray['Coming Soon'][] = $eachMessage;					
					$creatorMessages[$task['Creator']['id']]['Coming Soon'][] = $eachMessage;
                }
                
				$this->Task->id = $task['Task']['id'];
				$this->Task->saveField('last_notified_date', date('Y-m-d'));
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
						$digestMessage .= $dueTask['Task']['description'];
						$digestMessage .= '</li>'."\n";
            		}
            		$digestMessage .= '</ul>' . "\n";
            	}
            }
            $this->__sendMail($assigneeDetails['Assignee']['email'], 'Daily Task Digest', $digestMessage, $template = 'default');
        }
        
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
					$digestMessage .= $dueTask['Task']['description'];
					$digestMessage .= '</li>'."\n";
				}
				$digestMessage .= '</ul>';
			}
			
			$subject = $creators[$creator_id]['full_name'] ."'s Task Digest for ".date("m/d/Y");
			
			if($digestMessage!="")	{
				$this->__sendMail($creators[$creator_id]['email'],  $subject, $digestMessage, $template = 'default');
				break;
			}
		}
    }

	/** 
	 * send email notifications for overdue tasks
	 */

    function overdue_notify($assignee_id=null)   {

        $this->autoRender=false;
        $this->Task->recursive = 0;
        $conditions['AND'] = array('OR'=>array('Task.last_notified_date'=>null, 'Task.last_notified_date <>'=>date('Y-m-d')));
        $conditions['Task.assignee_id <>'] = null;       
        $conditions['OR'] = array(
				array('Task.is_completed' => 0),
				array('Task.is_completed' => null),
			);

        $conditions['Task.due_date <='] = date('Y-m-d');
       
        $allTasks = $this->Task->find('all', array('conditions'=>$conditions, 'order'=>'Task.due_date asc', 'fields'=>array('Task.id', 'Task.due_date', 'Task.assignee_id', 'Task.name', 'Task.description', 'Task.model', 'Task.foreign_key', 'Creator.email', 'Assignee.full_name', 'Assignee.email')));

        foreach($allTasks as $task) {
        	
        	$associated = $this->__findAssociated("Task", $task);

            if($assignee_id && $task['Task']['assignee_id']!=$assignee_id) continue;
            
            $taskLabel = $task['Task']['name'];
            
            if($associated)	{
            	$taskLabel .= ' : ' . $associated[$task['Task']['model']]['displayName'];
            }

            $message = '<p>You have a task which is over due.  If you\'ve received this message in error please login to mark the task as complete.  <a href="'. Router::url(array('controller'=>'tasks', 'plugin'=>'tasks', 'action'=>'view', $task['Task']['id']), true) . '">' . $taskLabel . '</a>' . '</p>';
            
            $subject = 'Overdue Task : Was due on' .  date('m/d/Y', strtotime($task['Task']['due_date']));
            //array($task['Assignee']['email'], $task['Creator']['email'])

            $this->__sendMail(array($task['Assignee']['email'], $task['Creator']['email']) , $subject, $message, $template = 'default');
           
            $this->Task->id = $task['Task']['id'];
            $this->Task->saveField('last_notified_date', date('Y-m-d'));
        }
    }
	
	/**
	 * @todo	 This send message thing is used here, and in the messages controller itself.  I don't know where we could put it so that its usable between both.  (Probably would have to do some kind of added on, slow component thing).
	 * @todo 	 The task messaging is for the entire task list.  It is not per task, like it should be.  But there is some thought that needs to be put in about who gets notifications for tasks.  Assignee, Assigner, and what if you want someone else in on it.  So the todo, is put in that thought and make it happen. 
	 */
	function _callback_commentsafterAdd($options) {		
		if ($this->request->params['action'] == 'view') :		
			$this->Task->recursive = 0;
			$task = $this->Task->find('first', array('conditions'=>array('Task.id'=>$options['modelId']), 'fields'=>array('Task.id', 'Task.due_date', 'Task.assignee_id', 'Task.name', 'Task.description', 'Task.model', 'Task.foreign_key', 'Creator.id', 'Creator.email', 'Creator.full_name', 'Assignee.email')));
			if(!empty($task))	{
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
				# send the message via email
			}
		endif;		
	}
	
	/**
	* To get "model" data for a task. Project in this case
	*
	*/
	
	function __findAssociated($curr_model, $data=array())	{
		$this->autoRender=false;
		if(!isset($data[$curr_model]['model']) || !isset($data[$curr_model]['foreign_key'])) return false;
		$plugin = pluginize($data[$curr_model]['model']);
		$model = $data[$curr_model]['model'];
		$init = !empty($plugin) ? $plugin . '.' . $model : $model;
		$foreignKey = $data[$curr_model]['foreign_key'];
		$Model = ClassRegistry::init($init);
		return $Model->find('first', array('conditions' => array($model.'.id' => $foreignKey), 'fields'=>array($Model->name  . '.' . $Model->displayField)));
	}
}