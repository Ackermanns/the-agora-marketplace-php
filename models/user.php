<?php
require_once './lib/abstractModel.php';
require_once './interfaces/IUserModel.php';

class User extends AbstractModel implements IUserModel {
	var $db;
	var $session;
	var $username;
	
	function __construct ($context){
		$this->db=$context->getDB();
		$this->session=$context->getSession();	
	}

	function setUser($username, $password) {
		// Method checks 
		$query = "SELECT username, passwordHash, businessId FROM users WHERE username=\"{$_POST['username']}\"";
		$result = $this->db->query($query);
		// Only one entry expected
		if (count($result) === 1) {
			// Validate username
			if ($result[0]['username'] !== $username) {
				return false;
			}
			//validate password
			else if (!password_verify($_POST['password'], $result[0]['passwordHash'])) {
				return false;
			}
			// valid login and can add
			else {
				$this->username = $result[0]['username'];
				$this->businessId = $result[0]['businessId'];
				$this->session->set("username", $result[0]['username']);
				$this->session->set("businessId", $result[0]['businessId']);
				$this->session->set("isLoggedIn", true);
				return true;
			}
		}
		// Return false otherwise
		return false;
	}

	function getUserDetails($username) {
		//$currentUser = $this->session->get('username');
    	$query = "SELECT businessId, username, firstName, lastName FROM users WHERE username=\"{$username}\"";
		$result = $this->db->query($query);
		return $result;
	}

	function getUserBusiness($businessId) {
		//$businessId = $this->session->get('businessId');
		$query = "SELECT businessId, name, locationBased, email, mobile, logo FROM business WHERE businessId=\"{$businessId}\"";
		$result = $this->db->query($query);
		return $result;
	}

	function getUserContacts() {
		$currentUser = $this->session->get('username');
		$query = "SELECT u.username, b.name FROM users AS u, business AS b, contacts AS c WHERE c.contact=u.username AND u.businessId=b.businessId AND c.user=\"{$currentUser}\"";
		$result = $this->db->query($query);
		return $result;
	}

	function getBusinessId($username) {
		$query = "SELECT businessId FROM users WHERE username=\"{$username}\"";
		$result = $this->db->query($query);
		return $result[0]['businessId'];
	}

	function addContact($adduser) {
		$CurrentUser = $this->session->get('username');
		// Check if user exists
		$query = "SELECT username FROM users WHERE username=\"{$adduser}\"";
		$result = $this->db->query($query);
		if (count($result) !== 0) {
			// Check if entry is already a contact
			$query = "SELECT * FROM contacts WHERE user=\"{$CurrentUser}\" AND contact=\"{$adduser}\"";
			$result = $this->db->query($query);
			if (count($result) === 0) {
				$query = "INSERT INTO theAgoraDB.CONTACTS (`user`, `contact`) VALUES(\"{$CurrentUser}\", \"{$_POST['addUsername']}\")";
				$this->db->execute($query);
			}
		}
	}

	function updateUser($ownerfname, $ownerlname, $bname, $blocationbased, $logoimage, $bemail, $bmobile) {
		$Currentuser = $this->session->get('username');
		$BusinssId = $this->getBusinessId($Currentuser);
		$businessDetails = $this->getUserBusiness($BusinssId);
		$currentBusinessLogo = $businessDetails[0]['logo'];
		try {
			// Update user profile
			if (strlen($bname) == 0) {
				throw new Exception("Business name cannot be left empty");
			}
			if (strlen($blocationbased) == 0) {
				throw new Exception("Location based cannot be left empty");
			}
			$query = "UPDATE users SET firstName=\"{$ownerfname}\", lastName=\"{$ownerlname}\" WHERE username=\"{$Currentuser}\"";
			$this->db->execute($query);

			// Check if new image
			if (file_exists($_FILES['logo']['tmp_name'])) {
				$newLogo = $_FILES['logo']['name'];
				$SaveFileDestination = 'images/businesses/'.$newLogo;
				echo $SaveFileDestination;
				move_uploaded_file($_FILES['logo']['tmp_name'], $SaveFileDestination);
			}
			// Keep old image if it is there
			else if ($businessDetails[0]['logo'] !== "placeholder.jpg") {
				$newLogo = $businessDetails[0]['logo'];
			}
			else {
				//No image change and keep placeholder
				$newLogo = "placeholder.jpg";
			}
			// Update business
			$query = "UPDATE business SET name=\"{$_POST['businessname']}\", locationBased=\"{$blocationbased}\", logo=\"{$newLogo}\", email=\"{$bemail}\", mobile=\"{$bmobile}\" WHERE businessId=\"{$BusinssId}\"";
			$this->db->execute($query);
		}catch(Exception $e) {
			return $e->getMessage();
		}
		
	}

	function registerNewUser($username, $password, $confirmpassword, $ownerfname, $ownerlname, $businessname, $locationbased, $email, $mobile, $logofile) {
		try {
			// Validate username
			$query = "SELECT username FROM users WHERE username = \"{$username}\"";
			$result = $this->db->query($query);
			if (strlen($username) == 0) {
				throw new Exception("Account must have a username");
			}
			if (count($result) !== 0) {
				throw new Exception("Username taken");
			}
			// Validate password
			if (strlen($password) == 0) {
				throw new Exception("Account must have a password");
			}
			if ($password !== $confirmpassword) {
				throw new Exception("Passwords do not match");
			}
			// Set passwordhash
			$passwordHash = password_hash($password, PASSWORD_DEFAULT);
			// Set logo
			if (!file_exists($_FILES['logo']['tmp_name']) || !is_uploaded_file($_FILES['logo']['tmp_name'])) {
				$imgName = "placeholder.jpg";
			}
			else {
				$imgName = $_FILES['logo']['name'];
				$SaveFileDestination = 'images/businesses/'.$imgName;
				move_uploaded_file($_FILES['logo']['tmp_name'], $SaveFileDestination);
			}
			// Validate business name
			if (strlen($businessname) == 0) {
				throw new Exception("Account must have a business name");
			}
			// Validate location based
			if (strlen($locationbased) == 0) {
				throw new Exception("Account must have a based location");
			}
			// Create business account primary key
			$query = "SELECT businessId FROM business";
			$result = $this->db->query($query);
			$nextId = count($result) + 1;
			//add new business entry to database
			$query = "INSERT INTO theagoradb.business (`businessId`,`name`,`locationBased`,`logo`,`email`,`mobile`,`verified`,`suspended`) VALUES(\"{$nextId}\",\"{$businessname}\",\"{$locationbased}\",\"{$imgName}\",\"{$email}\",\"{$mobile}\",\"1\",\"0\")";
			$this->db->execute($query);
			//add new accounts entry to database
			$query = "INSERT INTO theagoradb.users (`businessId`,`username`,`passwordHash`,`firstName`,`lastName`) VALUES (\"{$nextId}\",\"{$username}\",\"{$passwordHash}\",\"{$ownerfname}\",\"{$ownerlname}\")";
			$this->db->execute($query);
			return "success";
		} catch(Exception $e) {
			return $e->getMessage();
		}
	}
}

?>