<div class="tasks view">
  <div id="navigation">
    <div id="n1" class="info-block">
      <div class="viewRow">
        <ul class="metaData">
          <li><span class="metaDataLabel">
            <?php echo __('Subject : '); ?>
            </span><span class="metaDataDetail"><?php echo $task['Task']['name']; ?></span></li>
          <li><span class="metaDataLabel">
            <?php echo $model.' : '; ?>
            </span><span class="metaDataDetail"><?php echo $this->Html->link(strip_tags($task['Associated'][$model][$modelDisplayField]), array('plugin' => pluginize($model), 'controller' => Inflector::tableize($model), 'action' => 'view', $foreignKey)); ?></span></li>
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
  <?php $commentWidget->options(array('allowAnonymousComment' => false));?>
  <?php echo $commentWidget->display();?> </div>
  

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
