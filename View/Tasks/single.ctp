<div class="tasks view">
  <div id="navigation">
    <div id="n1" class="info-block">
      <div class="viewRow">
        <ul class="metaData">
          <li><span class="metaDataLabel">
            <?php echo __('Title : '); ?>
            </span><span class="metaDataDetail"><?php echo $task['Task']['name']; ?></span></li>
          <li><span class="metaDataLabel">
            <?php echo $model.' : '; ?>
            </span><span class="metaDataDetail"><?php echo $this->Html->link(strip_tags($task['Associated'][$model][$modelDisplayField]), array('plugin' => strtolower(pluginize($model)), 'controller' => Inflector::tableize($model), 'action' => 'view', $foreignKey), array('escape' => false)); ?></span></li>
          <li><span class="metaDataLabel">
            <?php echo __('Due : '); ?>
            </span><span class="metaDataDetail"><?php echo date('M d, y', strtotime($task['Task']['due_date'])); ?></span></li>
        </ul>
		<div class="recordData">
			<div class="truncate"><?php echo $task['Task']['description']; ?></div>
		</div>
      </div>
    </div>
    <!-- /info-block end -->
  </div>
</div>
<a name="comments"></a>
<div id="post-comments">
  <?php $this->CommentWidget->options(array('allowAnonymousComment' => false));?>
  <?php echo $this->CommentWidget->display();?> </div>
<?php 
// set the contextual menu items 
$completeAction = $task['Task']['is_completed'] == 1 ? $this->Html->link(__('Mark as Incomplete'), array('controller' => 'tasks', 'action' => 'incomplete', $task['Task']['id'])) : $this->Html->link(__('Mark as Complete'), array('controller' => 'tasks', 'action' => 'complete', $task['Task']['id']));

$this->set('quickNavAfterBack_callback', $this->Html->link(strip_tags($task['Associated'][$model][$modelDisplayField] . ' ' . $model), array('plugin' => strtolower(pluginize($model)), 'controller' => Inflector::tableize($model), 'action' => 'view', $foreignKey), array('escape' => false)));
echo $this->Element('context_menu', array('menus' => array(
	array(
		'heading' => 'Project Manager',
		'items' => array(
			$this->Html->link(__('Edit'), array('controller' => 'tasks', 'action' => 'edit', $task['Task']['id'])),
			$completeAction,
			),
		)
	)));
?>
  

<script type="text/javascript">
$(function() {
	$(".indexRow").parent().sortable({
		delay: 300,
		update: function(event, ui) {
			$('#loadingimg').show();
		 	var taskOrder = $(this).sortable('toArray').toString().replace(/row/g, '');
			$.post('/tasks/tasks/sort_order.json', {taskOrder:taskOrder}, 
				   function(data){
					  	var n = 1;
						$.each(data, function(i, item) {
							$('row.'+item).html(n);
							n++;
						});	
						$('#loadingimg').hide()
				   }
			);
		}
	});
});
</script>
