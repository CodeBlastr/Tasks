<div class="tasks index row">
	<div class="span4 col-md-4">
		<div class="panel-group" id="accordion">
		<?php foreach ($tasks as $key => $task) : ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title"><?php echo $this->Html->link($task['Task']['name'], '#childOf' . $task['Task']['id'], array('data-toggle' => 'collapse', 'data-target' => '#childOf' . $task['Task']['id'], 'data-parent' => '#accordion')); ?></h4>
				</div>
				<div class="panel-collapse collapse <?php echo $key == 0 ? 'in' : null; ?>" id="childOf<?php echo $task['Task']['id']; ?>">
				<?php foreach ($task['ChildTask'] as $child) : ?>
					<div class="panel-body">
						<?php echo $this->Html->link($child['name'], array('action' => 'view', $child['id'])); ?>
						<span class="badge"><?php echo $this->Html->link($child['Assignee']['username'], array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'index', 'filter' => 'assignee:' . $child['Assignee']['id'])); ?></span>
						<span class="badge"><?php echo ZuhaInflector::datify($child['due_date']); ?></span>
						<?php echo empty($child['Task']['is_completed']) ? '<span class="badge">' . $this->Html->link(__('Completed?'), array('action' => 'complete', $child['id']), null, __('Are you sure you want to complete %s?', $task['Task']['name'])) . '</span>' : null; ?>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	</div>
	
	<div class="span4 col-md-8">
		<?php
		// format calendar data and combine into one array
		foreach ( $tasks as $task ) {
			$events[] = array(
				'id' => $task['Task']['id'],
				'title' => __('%s - %s', $task['Task']['name'], $task['Assignee']['full_name']),
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

<?php echo $this->element('paging'); ?>

<script type="text/javascript">
$(function() {
	$('tbody').sortable({
		delay: 300,
		update: function(event, ui) {
			$('#loadingimg').show();
		 	var taskOrder = $(this).sortable('toArray').toString();
			$.post('/tasks/tasks/sort_order.json', {taskOrder:taskOrder}, 
				   function(data){
					  	var n = 1;
						$.each(data, function(i, item) {
							$('td.'+item).html(n);
							n++;
						});	
						$('#loadingimg').hide()
				   }
			);
		}
	});
});
</script>

<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Tasks',
		'items' => array(
			  $this->Html->link(__('New Task', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'edit')),
			  $this->Html->link(__('List All Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'index')),
			  $this->Html->link(__('My Complete Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'my', 'filter' => 'completed:1')),
			  $this->Html->link(__('My Incomplete Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'my')),
			  )
		)
	)));