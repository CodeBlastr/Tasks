<div class="tasks form">
<?php echo $form->create('Task');?>
	<fieldset>
 		<legend><?php __('Create Task');?></legend>
	<?php
		echo $form->input('Task.parent_id', array('empty' => true, 'label' => 'Which task list should this be on?'));
		echo $form->input('Task.name');
		echo $form->input('Task.description', array('type' => 'richtext'));
		echo $form->input('Task.due_date');
		echo $form->input('Task.order');
		echo $form->input('Task.assignee_id');
		echo $form->input('Task.model', array('type' => 'hidden'));
		echo $form->input('Task.foreign_key', array('type' => 'hidden'));
	?>
	</fieldset>
<?php echo $form->end('Submit');?>
</div>


<?php 
// set the contextual menu items
$menu->setValue(array(
	array(
		'heading' => 'Tasks',
		'items' => array(
			  $this->Html->link(__('My Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'my')),
			  $this->Html->link(__('List Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'index')),
			  ),
		),
	)
);
?>

