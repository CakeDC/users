<?php 
	echo $this->Paginator->counter(array(
		'format' => 'Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%')); 
?>