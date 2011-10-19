<?php
$items = array();
foreach($taskOrders as $key => $value) {
  $items[$key] = $value;
}
echo $this->Js->object($items);
?>