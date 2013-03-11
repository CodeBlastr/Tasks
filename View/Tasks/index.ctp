<div class="tasks index">
	<table cellpadding="0" cellspacing="0" class="table table-hover">
		<tr>
			<th>&nbsp;</th>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('due_date');?></th>
			<th><?php echo $this->Paginator->sort('assignee_id');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
		</tr>
		<?php
		$i = 0;
		foreach ($tasks as $task) {
		?>
		<tr id="<?php echo $task['Task']['id']; ?>">
			<td>
			<?php 
			echo $this->Element('thumb', array(
				'model' => 'Task', 
				'foreignKey' => $task['Task']['id'], 
				'showDefault' => 'false', 
				'thumbSize' => 'small', 
				'thumbLink' => array('plugin' => 'galleries', 'controller' => 'galleries', 'action' => 'view', 'Task', $task['Task']['id'])
				), 
				array('plugin' => 'galleries')); ?>
			</td>
			<td>
				<?php echo $this->Html->link($projects[$task['Task']['foreign_key']] . $task['Task']['displayName'], array('action' => 'view', $task['Task']['id']), array('escape' => false)); ?>
			</td>
			<td>
				<?php echo $this->Time->format('D, M j', $task['Task']['due_date']); ?>
			</td>
			<td>
				<?php echo $this->Html->link($task['Assignee']['username'], array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'index', 'filter' => 'assignee:' . $task['Assignee']['id'])); ?>
			</td>
			<td class="actions">
				<?php 
				echo $this->Html->link(__('Edit'), array('action' => 'edit', $task['Task']['id'])); 
				if (empty($this->request->params['named']['completed'])) {
					echo $this->Html->link(__('Complete'), array('action' => 'complete', $task['Task']['id']), null, sprintf(__('Are you sure you want to complete # %s?', true), $task['Task']['id']));
				}
				echo $this->Html->link(__('Delete'), array('action' => 'delete', $task['Task']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $task['Task']['id'])); ?>
			</td>
		</tr>
		<?php 
		} ?>
	</table>
</div>
<?php
echo $this->element('paging');

// set the contextual menu items
$this->set('context_menu', array('menus' => array(
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
	))); ?>
