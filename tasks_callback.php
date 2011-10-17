<?php

class TasksCallback	{
	function initialize(&$controller)	{
		//$this->Controller = &$Controller;
	}
	
	function beforeFilter(&$controller)	{
		$this->Controller = &$controller;
		
		if(isset($this->Controller->params['action']) && $this->Controller->params['action']=='runcron')	{
			
			App::import('Controller', 'Tasks.Tasks');
			$Tasks = new TasksController;
			$Tasks->SwiftMailer = $this->Controller->Component->_loaded['SwiftMailer'];
			$Tasks->__cron();
		}
	}
}

?>
