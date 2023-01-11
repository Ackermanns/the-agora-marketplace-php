<?php
require_once './lib/abstractController.php';
require_once './views/login.php';

class RegisterController extends AbstractController {
	private $path;
	private $conn;
	private $uri;
	public function __construct($context, $user) {
		parent::__construct($context);
		// This changes based on the path of the URL e.g marketplace, profile, etc.
		$this->path=$context->getURI()->getRemainingParts();
		$this->conn = $context->getDB();
		$this->uri = $context->getURI();
		$this->user=$user;
	}
	
	protected function getView($isPostback) {
		$view=new LoginView();
		$view->setTemplate('html/register.html');
		if ($isPostback === false) {
			// handle GET method
			$view->setTemplateField('errormessage','');
			return $view;
		}
		if ($isPostback === true) {
			// handle POST request
			// Validate username
			$message = $this->user->registerNewUser($_POST['username'], $_POST['password'], $_POST['confirmpassword'], $_POST['ownerfname'], $_POST['ownerlname'], $_POST['businessname'], $_POST['locationbased'], $_POST['email'], $_POST['mobile'], $_FILES['logo']);
			if ($message !== "success") {
				// Problem with logging in
				$view->setTemplateField('errormessage','<p style="color:red">'.$message.'</p>');
				return $view;
			}
			else {
				// Account created and can be allowed to log in
				$redirect = $this->uri->getRawUri()."login";
				header("Location: {$redirect}");
			}
			
			
		}
    }
}