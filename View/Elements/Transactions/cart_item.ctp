<?php
echo $this->element(
	'thumb',
	array(
	    'model' => 'CatalogItem',
	    'foreignKey' => $transactionItem['foreign_key'],
	    'thumbSize' => 'large',
	    'thumbLink' => '/catalogs/catalog_items/view/'.$transactionItem['foreign_key']
	    ),
	array('plugin' => 'galleries')
	);

echo '<span class="orderTransactionQuantity">' . $transactionItem['quantity'] . ' qty, of </span> ' . $transactionItem['name'];