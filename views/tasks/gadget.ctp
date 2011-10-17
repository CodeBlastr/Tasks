<div id="GadgetTasksContacts">
	<ul>
		<li><a href="#ContactAdd">Contact</a></li>
		<li><a href="#TaskAdd">Task</a></li>
	</ul>
	<div id="TaskAdd">
		<?php echo $this->Form->create('Task', array('url' => 'http://www2.razorit.com/tasks/tasks/add'));?>
		<table>
			<tr>
				<td>Project Name</td>
			</tr>
			<tr>
				<td><?php echo $this->Form->input('Task.parent_id', array('empty' => true, 'label' => false, 'style' => "width: 300px;")); ?></td>
			</tr>
			<tr>
				<td>Task Name</td>
				<td>Due Date</td>
				<td>Assignee</td>
			</tr>			
			<tr>
				<td><?php echo $this->Form->input('Task.name', array('label' => false)); ?></td>
				<td><?php echo $this->Form->input('Task.due_date', array('label' => false)); ?></td>
				<td><?php echo $this->Form->input('Task.assignee_id', array('label' => false)); ?></td>
				<?php
					echo $this->Form->input('Task.model', array('type' => 'hidden'));
					echo $this->Form->input('Task.foreign_key', array('type' => 'hidden'));
				?>
			</tr>
			<tr>
				<td align="right" colspan="3"><?php echo $this->Form->end('Submit');?></td>
			</tr>
		</table>
	</div>
	<div id="ContactAdd">
		<?php echo $this->Form->create('Contact', array('url' => 'http://www2.razorit.com/contacts/contacts/add'));?>
  		<table>
  			<tr>
  				<td>Contact Type</td>
				<td>Name</td>
  			</tr>
			<tr>
				<?php echo $this->Form->input('Contact.is_company', array('type' => 'hidden', 'value' => 1)); ?>
				<td><?php echo $this->Form->input('Contact.contact_type_id', array('empty'=>true, 'label' => false)); ?></td>
				<td><?php echo $this->Form->input('Contact.name', array('label' => false)); ?></td>
			</tr>
			<tr>
				<td>Detail Type</td>
				<td>Detail Value</td>
			</tr>
			<tr>
				<td><?php echo $this->Form->input('ContactDetail.0.contact_detail_type_id', array('label' => false)); ?></td>
				<td><?php echo $this->Form->input('ContactDetail.0.value', array('label' => false)); ?></td>
			</tr>
			<tr>
				<td align="right" colspan="3"><?php echo $this->Form->end('Submit');?></td>
			</tr>
		</table>		
	</div>
</div>

<script type="text/javascript">
	$("#GadgetTasksContacts").tabs();
</script>