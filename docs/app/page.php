<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		$classes_root = realpath(FW_PATH . '../main/vendor') . '/Morrow/Docs/';
		$id = $this->input->get('routed.id');
		$page_content = file_get_contents($classes_root . $id . '.md');
		$this->view->setContent('page_content', $page_content);
	}
}