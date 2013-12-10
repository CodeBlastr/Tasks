<div class="tasks view">
  <div id="navigation">
    <div id="n1" class="info-block">
      <div class="viewRow">
        <ul class="metaData">
         <li><span class="metaDataLabel">
            </span><span class="metaDataDetail"><?php echo $task['Task']['displayName']; ?></span></li>
          <li><span class="metaDataLabel">
            <?php echo __(' for '); ?>
            </span><span class="metaDataDetail"><?php echo $this->Html->link(strip_tags($task['Associated'][$model][$modelDisplayField]), array('plugin' => strtolower(ZuhaInflector::pluginize($model)), 'controller' => Inflector::tableize($model), 'action' => 'view', $foreignKey), array('escape' => false)); ?></span></li>
          <li><span class="metaDataLabel">
            <?php echo __(' before '); ?>
            </span><span class="metaDataDetail"><?php echo date('M d, Y', strtotime($task['Task']['due_date'])); ?></span></li>
        </ul>
		<div class="recordData">
			<?php echo $task['Task']['description']; ?>
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

$this->set('quickNavAfterBack_callback', $this->Html->link(strip_tags($task['Associated'][$model][$modelDisplayField] . ' ' . $model), array('plugin' => strtolower(ZuhaInflector::pluginize($model)), 'controller' => Inflector::tableize($model), 'action' => 'view', $foreignKey), array('escape' => false, 'class' => 'back')));
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Project Manager',
		'items' => array(
			$this->Html->link(__('Edit'), array('controller' => 'tasks', 'action' => 'edit', $task['Task']['id'])),
			$completeAction,
			),
		)
	)));
