<div class="row-fluid">
	<div class="tasks index span6 pull-left">
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
				echo $this->Element('Galleries.thumb', array(
					'model' => 'Task', 
					'foreignKey' => $task['Task']['id'], 
					'showDefault' => 'false', 
					'thumbSize' => 'small', 
					'thumbLink' => array(
						'plugin' => 'galleries', 'controller' => 'galleries', 'action' => 'view', 'Task', $task['Task']['id'],
						),
					'showEmpty' => false
					)); ?>
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
	
	<div class="span6 pull-right">
		<?php
		// format calendar data and combine into one array
		foreach ( $tasks as $task ) {
			$events[] = array(
				'id' => $task['Task']['id'],
				'title' => __('%s - %s', $task['Task']['displayName'], $task['Assignee']['full_name']),
				'allDay' => false,
				'start' => date('c', strtotime(__('%s 17:00:00', $task['Task']['due_date']))),
				'end' => date('c', strtotime(__('%s 18:00:00', $task['Task']['due_date']))),
				'url' => '/tasks/tasks/view/'.$task['Task']['id'],
				'className' => 'task',
				'color' => '#0d729a'
			);
		} 
		echo $this->Calendar->renderCalendar(array('data' => json_encode($events))); ?>
	</div>
</div>

<?php
echo $this->element('paging');

// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Tasks',
		'items' => array(
			  $this->Html->link(__('My'), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'my')),
			  $this->Html->link(__('Add'), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'add')),
			  $this->Html->link(__('List'), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'index')),
			  ),
		),
	))); ?>
