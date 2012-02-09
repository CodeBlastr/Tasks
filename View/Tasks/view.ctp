<div class="tasks view">
  <div id="navigation">
    <div id="n1" class="info-block">
      <div class="viewRow">
        <ul class="metaData">
          <li><span class="metaDataLabel">
            <?php echo __('Subject : '); ?>
            </span><span class="metaDataDetail"><?php echo $task['Task']['name']; ?></span></li>
        </ul>
        <div class="tasks form"> <?php echo $this->Form->create('Task', array('action' => 'add', 'type' => 'file'));?>
          <fieldset>
            <legend class="toggleClick">
            <?php echo __('Add a task to this list?');?>
            </legend>
            <?php
			 echo $this->Form->input('Task.parent_id', array('type' => 'hidden', 'value' => $parentId));
			 echo $this->Form->input('Task.name');
			 echo $this->Form->input('Task.due_date');
			 echo $this->Form->input('Task.assignee_id');
			 echo $this->Form->input('GalleryImage.filename', array('type' => 'file', 'label' => 'Upload your best image for this item.', 'after' => ' <p> You can add additional images after you save.</p>'));
		     echo $this->Form->input('GalleryImage.dir', array('type' => 'hidden'));
		     echo $this->Form->input('GalleryImage.mimetype', array('type' => 'hidden'));
		     echo $this->Form->input('GalleryImage.filesize', array('type' => 'hidden'));
			 echo $this->Form->input('Task.model', array('type' => 'hidden', 'value' => $model));
			 echo $this->Form->input('Task.foreign_key', array('type' => 'hidden', 'value' => $foreignKey));
			 echo $this->Form->input('Success.redirect', array('type' => 'hidden', 'value' => '/tasks/tasks/view/'.$parentId));
        	 echo $this->Form->end('Submit');
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
  <?php $this->CommentWidget->options(array('allowAnonymousComment' => false));?>
  <?php echo $this->CommentWidget->display();?> </div>