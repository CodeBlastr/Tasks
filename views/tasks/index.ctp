<div class="tasks index">
<table cellpadding="0" cellspacing="0">
	<tr>
		<th><?php echo $paginator->sort('name');?></th>
		<th><?php echo $paginator->sort('due_date');?></th>
		<th><?php echo $paginator->sort('assignee_id');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
<?php
$i = 0;
foreach ($tasks as $task):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?> id="<?php echo $task['Task']['id']; ?>">
		<td>
			<?php echo '<span class="taskType '.strtolower($task['Task']['model']).'">'.$task['Task']['model'].'</span> : '; echo $this->Html->link(__($task['Task']['name'], true), array('action' => 'view', $task['Task']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Time->format('D, M j', $task['Task']['due_date']); ?>
		</td>
		<td>
			<?php echo $this->Html->link($task['Assignee']['username'], array('controller' => 'users', 'action' => 'view', $task['Assignee']['id'])); ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $task['Task']['id'])); ?>
            <?php if ($this->params['named']['completed'] != 1) { ?>
			<?php echo $this->Html->link(__('Complete', true), array('action' => 'complete', $task['Task']['id']), null, sprintf(__('Are you sure you want to complete # %s?', true), $task['Task']['id'])); ?>
            <?php } ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $task['Task']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $task['Task']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php echo $this->element('paging'); ?>
<?php 
// set the contextual menu items
$this->Menu->setValue(array(
	array(
		'heading' => 'Tasks',
		'items' => array(
			  $this->Html->link(__('My Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'my')),
			  $this->Html->link(__('New Task', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'edit')),
			  $this->Html->link(__('List Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'index')),
			  ),
		),
	array(
		'heading' => 'Task Types',
		'items' => array(
			  $this->Html->link(__('List Enumerations', true), array('plugin' => null, 'controller' => 'enumerations', 'action' => 'index')),
			  $this->Html->link(__('New Enumeration', true), array('plugin' => null, 'controller' => 'enumerations', 'action' => 'add'))
			 ),
		),
	)
);
?>
