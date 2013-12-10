<?php
/**
 * Task helper
 *
 * @package 	tasks
 * @subpackage 	tasks.views.helpers
 */
class TaskHelper extends AppHelper {

/**
 * helpers variable
 *
 * @var array
 */
	public $helpers = array ('Html', 'Form', 'Js' => 'Jquery');

/**
 * Constructor method
 * 
 */
    public function __construct(View $View, $settings = array()) {
    	$this->View = $View;
    	//$this->defaults = array_merge($this->defaults, $settings);
		parent::__construct($View, $settings);
		App::uses('Task', 'Tasks.Model');
		$this->Task = new Task();
    }

/**
 * Find method
 */
 	public function find($type = 'first', $params = array()) {
 		return $this->Task->find($type, $params);
 	}

}