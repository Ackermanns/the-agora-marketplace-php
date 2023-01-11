<?php
	include 'lib/context.php';	
	include 'models/user.php';
	try {
	// common stuff can go here
	
		$context=Context::createFromConfigurationFile("website.conf");
		$user = new User($context);
		$context->setUser($user);

		// getController will throw an exception if the request path is invalid
		// or the user is not authorised for access
		$controller=getController($context->getURI(), $context->getSession());
		// the approach below is called "convention over configuration"
		// rather than configuring separate class names and routes, we base
		// the pattern on naming and site organisation conventions
		$controllerPath='controllers/'.strtolower($controller).'Controller.php';
		$controllerClass=$controller.'Controller';
		if (isset($controllerPath)) {
			require $controllerPath;
		}
	} catch (Exception $ex) {
		logException ($ex);
		echo 'Page not found<br/>';
		// see comments in next try catch block
		exit;
	}
	// At this stage, we know the controller and that the user is authorised
	// So we just run the selected controller
	try {
		$actor = new $controllerClass($context, $user);
		$actor->process();
	} catch (Exception $ex) {
		logException ($ex);

		//Do not want to display errors for now
		//echo $ex.getMessage().'<br/>';

	}
	
	function logException ($ex) {
		// do nothing for now
	}
	
	// This is a page router
	// The URI is matched to the first half of the controller name
	function getController($uri, $session) {
		$path=$uri->getPart();
		if ($session->isKeySet("isLoggedIn")) {
			if ($session->get("isLoggedIn") === true) {
				switch ($path) {
					case '':
						$uri->prependPart('marketplace');
						return 'Static';
					case 'static':
						return 'Static';
					default:
						echo '<iframe src="https://giphy.com/embed/Ju7l5y9osyymQ" width="480" height="360" frameBorder="0" allowFullScreen></iframe><br>404 not found';
						throw new InvalidRequestException ("No such page");
				}
			}
		}
		if ($path === 'register') {
			return 'Register';
		}
		else {
			// user is not a member and needs to log in
			return 'Login';
		}
	}

?>