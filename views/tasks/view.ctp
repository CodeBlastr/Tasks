<div class="tasks view">
  <div id="navigation">
    <div id="n1" class="info-block">
      <div class="viewRow">
        <ul class="metaData">
          <li><span class="metaDataLabel">
            <?php __('Subject : '); ?>
            </span><span class="metaDataDetail"><?php echo $task['Task']['name']; ?></span></li>
        </ul>
        <div class="tasks form"> <?php echo $form->create('Task', array('action' => 'add'));?>
          <fieldset>
            <legend class="toggleClick">
            <?php __('Add a task to this list?');?>
            </legend>
            <?php
			 echo $form->input('Task.parent_id', array('type' => 'hidden', 'value' => $parentId));
			 echo $form->input('Task.name');
			 echo $form->input('Task.due_date');
			 echo $form->input('Task.assignee_id');
			 echo $form->input('Task.model', array('type' => 'hidden', 'value' => $model));
			 echo $form->input('Task.foreign_key', array('type' => 'hidden', 'value' => $foreignKey));
			 echo $this->Form->input('Success.redirect', array('type' => 'hidden', 'value' => '/tasks/tasks/view/'.$parentId));
        	 echo $form->end('Submit');
			?>
          </fieldset>
        </div>
        <?php echo $this->Element('scaffolds/index', array('data' => $childTasks)); ?>
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
