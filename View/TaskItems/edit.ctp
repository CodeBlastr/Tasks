<div class="tasks form">
<?php echo $this->Form->create('Task', array('action' => 'edit', 'type' => 'file'));?>
	<fieldset>
 		<legend><?php echo __('Edit Task');?></legend>
	<?php
		echo $this->Form->input('Task.id');
		echo $this->Form->input('Task.name');
		echo $this->Form->input('Task.parent_id', array('label' => 'Task List', 'empty' => true));
		echo $this->Form->input('Task.description', array('type' => 'richtext'));
		echo $this->Form->input('Task.due_date');
		echo $this->Form->input('Task.order');
		echo $this->Form->input('Task.assignee_id', array('empty' => true));
		echo $this->Form->input('GalleryImage.filename', array('type' => 'file', 'label' => 'Upload your best image for this item.', 'after' => ' <p> You can add additional images after you save.</p>'));
	    echo $this->Form->input('GalleryImage.dir', array('type' => 'hidden'));
	    echo $this->Form->input('GalleryImage.mimetype', array('type' => 'hidden'));
	    echo $this->Form->input('GalleryImage.filesize', array('type' => 'hidden'));
		echo $this->Form->input('Task.model', array('type' => 'hidden'));
		echo $this->Form->input('Task.foreign_key', array('type' => 'hidden'));
	?>
	</fieldset>
<?php echo $this->Form->end('Submit');?>
</div>


<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Tasks',
		'items' => array(
			  $this->Html->link(__('My Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'my')),
			  $this->Html->link(__('Delete', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'delete', $this->Form->value('Task.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Task.id'))),
			  $this->Html->link(__('List Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'index')),
			  ),
		),
	)));
?>

