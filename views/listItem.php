<?php
require_once './lib/abstractView.php';

class ListItemView extends AbstractView {
    private $content = 'No List Item content';

    public function prepare () {
		$this->content=file_get_contents("html/listItem.html");
    }

    public function getContent() {
        return $this->content;
    }
}
