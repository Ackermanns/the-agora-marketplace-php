<?php
require_once './lib/abstractView.php';

class ItemView extends AbstractView {
    private $content = 'No Profile content';
    private $uri;
    private $ref;
    private $listings;

    public function __construct($listings, $uri, $session, $ref) {
      $this->listings = $listings;
      $this->uri = $uri;
      $this->session = $session;
     $this->ref = $ref;
    }

    public function prepare () {
      
      // Get data
      $result = $this->listings->getItemData($this->ref);
      $soldResult = $this->listings->isSold($this->ref);
      $imgLink = $imgLink = $this->uri->getSite().'images/listings/'.$result[0]['itemImage'];


      $this->content = '<h2>'.$result[0]["title"].'</h2>'.
      '<p>Cost: $'.$result[0]['price'].' '.$result[0]["rate"].'</p>'.
      '<p>Description: '.$result[0]['itemDescription'].'</p>'.
      '<p>Hashtags: '.$result[0]["hashtags"].'</p>'.
      '<br><br>'.
      '<h2>Sellers Listing details</h2>'.
      '<p>Seller: '.$result[0]["seller"].'</p>'.
      '<p>Date listed: '.$result[0]["datePosted"].'</p>'.
      '<br><br>'.
      '<img src="'.$imgLink.'" width="250" height="auto"/><br><br>';
      
      if ($soldResult[0]['count'] == 0) {
        // Item is avaliable to buy
        $this->content .= '<form  method="POST" action="##site##static/itemView/'.$this->ref.'">'.
          '<button type="submit">Buy Now</button>'.
          '</form>';
      }
      else {
        // Item sold
        $soldResult = $this->listings->getSoldUsers($this->ref);
        $BuyerDetails = $this->listings->getSoldUserDetails($soldResult[0]['buyer']);
        $SellerDetails = $this->listings->getSoldUserDetails($soldResult[0]['seller']);
        $this->content .= '<h1>Transaction details</h1>'.
          '<h2>Buyer Details</h2>'.
          '<p>Company: '.$BuyerDetails[0]['name'].'</p>'.
          '<p>email: '.$BuyerDetails[0]['email'].'</p>'.
          '<p>mobile: '.$BuyerDetails[0]['mobile'].'</p>'.
          '<h2>Seller Details</h2>'.
          '<p>Company: '.$SellerDetails[0]['name'].'</p>'.
          '<p>email: '.$SellerDetails[0]['email'].'</p>'.
          '<p>mobile: '.$SellerDetails[0]['mobile'].'</p>';
      }
    }

    public function getContent() {
        return $this->content;
    }
}