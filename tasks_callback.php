<?php

class TasksCallback	{
	function initialize(&$controller)	{
		//$this->Controller = &$Controller;
	}
	
	function beforeFilter(&$controller)	{
		
		$this->Controller = &$controller;

		if(isset($this->Controller->request->params['action']) && $this->Controller->request->params['action']=='runcron')	{
			App::import('Controller', 'Tasks.Tasks');
			$Tasks = new TasksController;
			//debug($this->Controller);die;
			//$Tasks->SwiftMailer = $this->Controller->Component->_loaded['SwiftMailer'];
			$Tasks->__cron();
		}
	}
}

?>
