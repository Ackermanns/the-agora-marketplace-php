<?php
require_once './lib/abstractView.php';

class StaticView extends AbstractView {
	// The static view class handles static content in the template inheritance
	// The content is an exception that is handled in different models
    private $session;

    public function __construct($session) {
		$this->session = $session;
    }


	/*
	public function prepare () {
		$content=$this->getModel()->getContent();
		
		$this->setTemplateField('content',$content);
	}
	*/

	public function setNav($isAdmin) {
		$user = $this->session->get('username');
		$navContent = 
			'<li class="nav-item"><a class="nav-link" href="##site##static/marketplace" >Marketplace</a></li>'.
			'<li class="nav-item"><a class="nav-link" href="##site##static/profile/'.$user.'/view" >Profile</a></li>'.
			'<li class="nav-item"><a class="nav-link" href="##site##static/purchased" >Purchased</a></li>'.
			'<li class="nav-item"><a class="nav-link" href="##site##static/sold" >Sold</a></li>'.
			'<li class="nav-item"><a class="nav-link" href="##site##static/myListings" >My Listings</a></li>'.
			'<li class="nav-item"><a class="nav-link" href="##site##static/listitem" >List Item</a></li>'.
			'<li class="nav-item"><a class="nav-link" href="##site##static/logout" >Logout</a></li>';
		$this->setTemplateField('navbar',$navContent);
	}
}
