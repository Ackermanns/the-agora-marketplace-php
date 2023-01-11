<?php
require_once './lib/abstractModel.php';

// Model is responsable for listing based content
class ListingsModel extends AbstractModel {
	private $db;
	private $uri;
	private $session;
	private $user;
	
	public function __construct($db, $uri, $session, $user) {
		$this->db = $db;
		$this->uri = $uri;
		$this->session = $session;
		$this->user = $user;
	}

	public function getMyListings() {
		//Gets user listed listings
		$user = $this->session->get('username');
		$query = "SELECT l.referenceCode, l.title, l.price, l.itemImage, l.itemDescription, l.hashtags FROM listings AS l LEFT JOIN sold ON l.referenceCode = sold.referenceCode WHERE sold.seller is null AND l.seller=\"{$user}\"";
		$result = $this->db->query($query);
		return $result;
	}

	public function getMarketplaceListings() {
		//Standerd marketplace query
		$query = "SELECT l.referenceCode, l.title, l.price, l.itemImage, l.itemDescription, l.hashtags FROM listings AS l LEFT JOIN sold ON l.referenceCode = sold.referenceCode WHERE sold.seller is null AND sold.buyer is null";
		// Apply search terms (if any) to marketplace query
		$search = $this->session->get('searchTerms');
		if (count($search) !== 0) {
			$query .= " AND ";
			foreach ($search as $s) {
				$query .= "l.hashtags LIKE \"%{$s}%\" AND ";
			}
			//Trim last 'OR '
			$query = substr($query, 0, -4);
		}
		$result = $this->db->query($query);
		return $result;
    }

	public function getSoldListings() {
		$currentuser = $this->session->get('username');
		$query = "SELECT * FROM sold, listings WHERE sold.referenceCode = listings.referenceCode AND sold.seller=\"{$currentuser}\"";
		$result = $this->db->query($query);
		return $result;
	}

	public function getPurchasedListings() {
		$currentuser = $this->session->get('username');
		$query = "SELECT * FROM sold, listings WHERE sold.referenceCode = listings.referenceCode AND sold.buyer=\"{$currentuser}\"";
		$result = $this->db->query($query);
		return $result;
	}


	public function getItemData($ref) {
		$query = "SELECT seller, datePosted, title, itemDescription, price, rate, itemImage, hashtags FROM listings WHERE referenceCode=\"{$ref}\"";
		$result = $this->db->query($query);
		return $result;
	}

	public function getSoldUsers($ref) {
		$query = "SELECT seller, buyer FROM sold WHERE referenceCode=\"{$ref}\"";
		$result = $this->db->query($query);
		return $result;
	}

	public function getSoldUserDetails($ref) {
		$query = "SELECT b.name, b.email, b.mobile FROM business AS b, users, sold WHERE b.businessId=users.businessId AND users.username=\"{$ref}\"";
		$result = $this->db->query($query);
		return $result;
	}

	public function isSold($ref) {
		// A method to check whenever an item is sold or not for the purposes of viewing the item content
		$query = "SELECT count(*) as count FROM sold WHERE referenceCode=\"{$ref}\"";
    	$result = $this->db->query($query);
		return $result;
	}

	public function buyItem($ref) {
		$currentuser = $this->session->get('username');
		$dateStamp = date("Y-m-d");
		$query = "SELECT seller FROM listings WHERE referenceCode=\"{$ref}\"";
		$sellerResult = $this->db->query($query);
		// Insert into sold table
		$query = "INSERT INTO theAgoraDB.sold (`referenceCode`, `seller`, `buyer`, `dateSold`) VALUES('{$ref}', '{$sellerResult[0]['seller']}', '{$currentuser}', '{$dateStamp}');";
		echo $query;
		$this->db->execute($query);
	}

	public function listItem($reference, $title, $description, $price, $rate, $imageFile, $hashtags) {
		try {
			$currentuser = $this->session->get('username');
			$dateStamp = date("Y-m-d");
			// Validate title
			if (strlen($title) == 0) {
				throw new Exception("Listing must have a title");
			}
			//NOTE: for now assumes all image names are unique in folder
			// Set logo
			if (!file_exists($imageFile['tmp_name']) || !is_uploaded_file($imageFile['tmp_name'])) {
				$imgName = "placeholder.jpg";
			}
			else {
				$imgName = $imageFile['name'];
				$SaveFileDestination = 'images/listings/'.$imgName;
				move_uploaded_file($imageFile['tmp_name'], $SaveFileDestination);
			}
			
			//Commit listing entry to DB
			$query = "INSERT INTO theAgoraDB.listings (`referenceCode`, `seller`, `datePosted`, `title`, `itemDescription`, `price`, `rate`,`itemImage`,`hashtags`) VALUES".
				"(\"{$reference}\", \"{$currentuser}\", \"{$dateStamp}\", \"{$title}\", \"{$description}\", \"{$price}\", \"{$rate}\", \"{$imgName}\", \"{$hashtags}\")";
			echo $query;
			$this->db->execute($query);
			return "success";
		} catch(Exception $e) {
			return $e->getMessage();
		}
	}
}
