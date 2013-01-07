<?php

namespace Morrow\Libraries;

include(FW_PATH . '_user/externals/test.class.php');

class Test extends \Test {
	public function get() {
		return 'Adapter "Test" initialized.';
	}
}