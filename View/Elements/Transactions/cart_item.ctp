<?php
echo '<div style="display: inline-block;">';
echo $this->Html->link($transactionItem['name'], '/tasks/tasks/view/'.$transactionItem['foreign_key'], array(), 'Are you sure you want to leave this page?');
echo '</div>';
echo $this->element(
	'thumb',
	array(
	    'model' => 'Task',
	    'foreignKey' => $transactionItem['foreign_key'],
	    'thumbSize' => 'small',
	    'thumbWidth' => 75,
	    'thumbHeight' => 75,
	    'thumbLink' => '/tasks/tasks/view/'.$transactionItem['foreign_key']
	    ),
	array('plugin' => 'galleries')
	);
echo $this->Form->input("TransactionItem.{$i}.quantity", array(
    'label' => false,
    'div' => array('style' => 'display:inline-block'),
    'value' => $transactionItem['quantity'],
    'size' => 1
    ));
