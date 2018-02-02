<?php
/**
 * Copyright 2009 - 2018, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2018, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="paging">
	<?php
	echo $this->Paginator->prev('< ' . __d('users', 'previous'), [], null, ['class' => 'prev disabled']);
	echo $this->Paginator->numbers(['separator' => '']);
	echo $this->Paginator->next(__d('users', 'next') . ' >', [], null, ['class' => 'next disabled']);
	?>
</div>
