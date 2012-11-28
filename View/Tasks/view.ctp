<div class="well well-large pull-right last span3">
	<span class="label label-info"><?php echo !empty($task['Task']['due_date']) ? Inflector::humanize($task['Task']['due_date']) : 'No Due Date'; ?> </span>
	<hr />
	<div class="tasks form"> 
		<?php 
		echo $this->Html->link(__('Convert to a task list?'), '', array('class' => 'btn btn-block toggleClick', 'data-target' => '#TaskAddForm', 'rel' => 'tooltip', 'title' => __('Change this task to a task list by adding a sub task.')));
		echo $this->Form->create('Task', array('action' => 'add', 'type' => 'file'));
		echo $this->Form->input('Task.parent_id', array('type' => 'hidden', 'value' => $parentId));
		echo $this->Form->input('Task.name');
		echo $this->Form->input('Task.due_date');
		echo $this->Form->input('Task.assignee_id', array('empty' => '-- Select --'));
		echo $this->Form->input('GalleryImage.filename', array('type' => 'file', 'label' => 'Task thumbnail image (optional)'));
		echo $this->Form->input('GalleryImage.dir', array('type' => 'hidden'));
		echo $this->Form->input('GalleryImage.mimetype', array('type' => 'hidden'));
		echo $this->Form->input('GalleryImage.filesize', array('type' => 'hidden'));
		echo $this->Form->input('Task.model', array('type' => 'hidden', 'value' => $model));
		echo $this->Form->input('Task.foreign_key', array('type' => 'hidden', 'value' => $foreignKey));
		echo $this->Form->input('Success.redirect', array('type' => 'hidden', 'value' => '/tasks/tasks/view/'.$parentId));
		echo $this->Form->end('Submit'); ?>
	</div>
</div>



<div class="tasks view">
	<?php
	echo __('<p>%s</p>', $task['Task']['description']); 
	echo !empty($childTasks) ?  $this->Element('scaffolds/index', array('data' => $childTasks)) : null; ?>
</div>

<?php /* Removed because we should be using the messages plugin, not the comments plugin
<a name="comments"></a>
<div id="post-comments">
  <?php $this->CommentWidget->options(array('allowAnonymousComment' => false));?>
  <?php echo $this->CommentWidget->display();?>
</div> */ ?>

<?php
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Related',
		'items' => array(
			$this->Html->link(__('View %s', $task['Associated'][$task['Task']['model']]['name']), array('plugin' => ZuhaInflector::pluginize($task['Task']['model']), 'controller' => Inflector::tableize($task['Task']['model']), 'action' => 'view', $task['Task']['foreign_key'])),
			),
		),
	))); ?>