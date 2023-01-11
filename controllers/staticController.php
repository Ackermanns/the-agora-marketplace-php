<?php

require_once './lib/abstractController.php';
require_once './models/static.php';
require_once './views/static.php';
require_once './models/listings.php';
// The views that will dictate page content
require_once './views/listItem.php';
require_once './views/editProfile.php';
require_once './views/marketplace.php';
require_once './views/profile.php';
require_once './views/itemView.php';
require_once './views/purchased.php';
require_once './views/sold.php';
require_once './views/myListings.php';

 // The static controller will handle most of the non-changing parts of the
 // page, eg template inheritance

class StaticController extends AbstractController {
	private $path;
	private $context;
	private $listings;
	private $user;
	public function __construct($context, $user) {
		parent::__construct($context);
		// This changes based on the path of the URL e.g marketplace, profile, etc.
		$this->path=$context->getURI()->getRemainingParts();
		$this->context = $context;
		$this->user = $user;
		$this->listings = new ListingsModel($this->context->getDB(), $this->context->getURI(), $this->context->getSession(), $user);
	}
	
	protected function getView($isPostback) {
		$Currentuser = $this->context->getSession()->get('username');
		$CurrentbusinessId = $this->context->getSession()->get('businessId');
		$view=new StaticView($this->context->getSession());
		$view->setTemplate('html/masterPage.html');
		$view->setTemplateField('errormessage','');
		$path=explode("/",$this->path);
		//GET request
		if ($isPostback === false) {
			// create output
			
			// the page content router
			$viewSelect = $this->path.'View';
			switch ($path[0]) {
				case 'marketplace':
					$contentView = new MarketplaceView($this->listings, $this->context->getURI(), $this->context->getSession());
					$view->setTemplateField('pagename','Marketplace');
					break;
				case 'profile':
					$action = $path[2];
					if ($action == 'view') {
						$contentView = new ProfileView($this->context->getDB(), $this->context->getURI(), $this->context->getSession(), $this->user);
						$view->setTemplateField('pagename','Profile');
					}
					break;
				case 'listitem':
					$contentView = new ListItemView();
					$view->setTemplateField('pagename','List an Item');
					break;
				case 'itemView':
					// ...static/viewItem/*ref num* format
					// expect path to contain 2 items
					$contentView = new ItemView($this->listings, $this->context->getURI(), $this->context->getSession(), $path[1]);
					$view->setTemplateField('pagename','');
					break;
				case 'sold':
					// seller seeing their sold items
					$contentView = new SoldView($this->listings, $this->context->getURI());
					$view->setTemplateField('pagename','Sold');
					break;
				case 'purchased':
					// buyer seeing their purchased items
					$contentView = new PurchasedView($this->listings, $this->context->getURI());
					$view->setTemplateField('pagename','Purchased');
					break;
				case 'myListings':
					// buyer seeing their purchased items
					$contentView = new MyListingsView($this->listings, $this->context->getURI());
					$view->setTemplateField('pagename','My Listings');
					break;
				case 'logout':
					// user wishes to log out
					$redirect = $this->context->getURI()->getSite()."login";
					$this->context->getSession()->clear();
					header("Location: {$redirect}");
				
				default:
					echo '<iframe src="https://giphy.com/embed/Ju7l5y9osyymQ" width="480" height="360" frameBorder="0" class="giphy-embed" allowFullScreen></iframe><p><a href="https://giphy.com/gifs/rick-astley-Ju7l5y9osyymQ">via GIPHY</a></p><br>404 not found';
					throw new InvalidRequestException ("No such page");
			}
			// Load content view into the static view
			$contentView->prepare();
			$getContent = $contentView->getContent();
			$view->setTemplateField('content',$getContent);
			$view->setNav(true);
			return $view;
		}
		else if ($isPostback === true) {
			$dateStamp = date("Y-m-d");
			switch ($path[0]) {
				case 'marketplace':
					$search = $this->context->getSession()->get('searchTerms');
					if ($path[1] === "add") {
						if (strlen($_POST['search']) !== 0) {
							$search[] = $_POST['search'];
							$this->context->getSession()->set("searchTerms", $search);
						}
					}
					else if ($path[1] === "clear") {
						$this->context->getSession()->unsetKey('searchTerms');
						$this->context->getSession()->set("searchTerms", array());
					}
					$redirect = $this->context->getURI()->getSite().'static/marketplace';
					header("Location: {$redirect}");
					break;
				case 'itemView':
					//Item purchased

					$this->listings->buyItem($path[1]);
					$redirect = $this->context->getURI()->getSite().'static/purchased';
					header("Location: {$redirect}");
					break;
				case 'listitem':
					//Item listed
					$reference = $this->GenerateReferenceCode();
					$message = $this->listings->listItem($reference, $_POST['title'], $_POST['description'], $_POST['price'], $_POST['rate'], $_FILES['imagefile'], $_POST['hashtags']);
					if ($message !== "success") {
						$contentView = new ListItemView();
						// Load content view into the static view
						$contentView->prepare();
						$getContent = $contentView->getContent();
						$view->setTemplateField('content',$getContent);
						$view->setTemplateField('pagename','List an Item');
						$view->setNav(true);
						$view->setTemplateField('errormessage','<p style="color:red">'.$message.'</p>');
						return $view;
					}
					else {
						// Listing successful, go to my listings page
						header("Location: marketplace");
					}
					break;
				case 'profile':
					if ($path[2] === "add") {
						echo $_POST['addUsername'];
						$this->user->addContact($_POST['addUsername']);
						$redirect = $this->context->getURI()->getSite().'static/profile/'.$Currentuser.'/view';
						header("Location: {$redirect}");
					}
					else if ($path[2] === "edit") {
						$contentView = new EditProfileView($this->user, $this->context->getURI(), $this->context->getSession());
						$view->setTemplateField('pagename','Update Account');
						$contentView->prepare();
						$getContent = $contentView->getContent();
						$view->setTemplateField('content',$getContent);
						// Setup static page content
						$view->setNav(true);
						return $view;
					}
					else if ($path[2] === "update") {
						$message = $this->user->updateUser($_POST['ownerfname'], $_POST['ownerlname'], $_POST['businessname'], $_POST['locationbased'], $_FILES['logo'], $_POST['email'], $_POST['mobile']);
						if ($message !== "success") {
							$contentView = new EditProfileView($this->user, $this->context->getURI(), $this->context->getSession());
							$view->setTemplateField('pagename','Update Account');
							$view->setTemplateField('errormessage','<p style="color:red">'.$message.'</p>');
							$contentView->prepare();
							$getContent = $contentView->getContent();
							$view->setTemplateField('content',$getContent);
							// Setup static page content
							$view->setNav(true);
							return $view;
						}
						else {
							$redirect = $this->context->getURI()->getSite().'static/profile/'.$Currentuser.'/view';
							header("Location: {$redirect}");
						}
					}
			}
		}
		else {
			throw new InvalidRequestException ("No such page");
		}
	}

	public function GenerateReferenceCode(): string {
		 //Generate reference for new listing
		 $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		 $referenceGenerated = "";
		 $valid = false;
		 while ($valid === false) {
			//Generate
			for ($i = 0; $i <= 10; $i++) {
				$index = rand(0, strlen($characters) - 1);
				$referenceGenerated .= $characters[$index];
				$valid = true;
			}
		 }
		 //NOTE: likely that the randomly generated reference will be unique
		 return $referenceGenerated;
	}
}

?>