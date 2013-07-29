<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		$classes_root = realpath(FW_PATH . '../frontend/vendor') . '/Morrow/Docs/';
		$id = $this->input->get('id');
		$page_content = file_get_contents($classes_root . $id . '.md');
		$this->view->setContent('page_content', $page_content);
	}
}