<div class="tasks form">
<?php echo $form->create('Task', array('action' => 'edit'));?>
	<fieldset>
 		<legend><?php __('Edit Task');?></legend>
	<?php
		echo $form->input('Task.id');
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
			  $this->Html->link(__('Delete', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'delete', $form->value('Task.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $form->value('Task.id'))),
			  $this->Html->link(__('List Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'index')),
			  ),
		),
	)
);
?>

