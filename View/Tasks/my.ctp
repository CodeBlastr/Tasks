<div class="mytasks index">
<table cellpadding="0" cellspacing="0">
<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('order');?></th>
		<th><?php echo $this->Paginator->sort('name');?></th>
		<th><?php echo $this->Paginator->sort('due_date');?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
</thead>
<tbody>
<?php
$i = 0;
foreach ($tasks as $task):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?> id="<?php echo $task['Task']['id']; ?>">
		<td class="<?php echo $task['Task']['id']; ?>">
			<?php echo $task['Task']['order']; ?>
		</td>
		<td>
			<?php echo $this->Html->link($projects[$task['Task']['foreign_key']] . ' : ' . $task['Task']['name'], array('action' => 'view', $task['Task']['id']), array('escape' => false)); ?>
		</td>
		<td>
			<?php echo $this->Time->format('M d, Y', $task['Task']['due_date']); ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $task['Task']['id'])); ?>
            <?php if ($this->request->params['named']['completed'] != 1) { ?>
			<?php echo $this->Html->link(__('Complete', true), array('action' => 'complete', $task['Task']['id']), null, sprintf(__('Are you sure you want to complete # %s?', true), $task['Task']['id'])); ?>
            <?php } ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $task['Task']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $task['Task']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php echo $this->Element('paging'); ?>

<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Tasks',
		'items' => array(
			  $this->Html->link(__('New Task', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'edit')),
			  $this->Html->link(__('List All Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'index')),
			  $this->Html->link(__('My Completed Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'my', 'completed' => 1)),
			  $this->Html->link(__('My Incomplete Tasks', true), array('plugin' => 'tasks', 'controller' => 'tasks', 'action' => 'my')),
			  ),
		),
	)));
?>


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