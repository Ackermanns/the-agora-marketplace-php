<?php

require_once './lib/abstractController.php';
include './views/login.php';


 class loginController extends AbstractController {
	private $path;
	private $conn;
	private $uri;
	private $session;
	private $user;
	public function __construct($context, $user) {
		parent::__construct($context);
		$this->path=$context->getURI()->getSite();
		$this->conn=$context->getDB();
		$this->uri=$context->getURI();
		$this->session=$context->getSession();
		$this->user=$user;
	}

    protected function getView($isPostback) {
		$view=new LoginView();
		$view->setTemplate('html/login.html');
		if ($isPostback === false) {
			//GET request handler
			$view->setTemplateField('errormessage','');
			return $view;
		}
		if ($isPostback === true) {
			//POST request handler
			try {
				$verified = $this->user->setUser($_POST['username'], $_POST['password']);
				if ($verified === false) {
					throw new InvalidRequestException();
				}
				else {
					// Otherwise validated
					$this->session->set("searchTerms", array());
					$redirect = $this->uri->getRawUri()."static/marketplace";
					header("Location: {$redirect}");
				}
				
			} catch(Exception $e) {
				// Not valid login
				$view->setTemplateField('errormessage','<p style="color:red">Could Not Login, please try again</p>');
				return $view;
			}
		}

	}
 }