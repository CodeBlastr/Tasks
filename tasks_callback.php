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
			$Tasks->SwiftMailer = $this->Controller->SwiftMailer;
			$Tasks->__cron();
		}
	}
}
?>
