<?php
require_once './lib/abstractView.php';

class SoldView extends AbstractView {
    private $content = 'No Profile content';
    private $conn;
    private $uri;
    private $session;

    public function __construct($listings, $uri) {
      $this->listings = $listings;
      $this->uri = $uri;
    }

    public function prepare () {
      //Prepare the content from models and context
      $result = $this->listings->getSoldListings();

      // Generate HTML page content
      $this->content = '<table class="table table-striped table-dark\"><tbody>';
      foreach ($result as $listing) {
        $this->content.='<tr>'.
          '<td class="col-3"><img src="'.$this->uri->getSite().'images/listings/'.$listing['itemImage'].'" width="250" height="auto"/></td>'.
          '<td><p class="display-6"><a href="'.$this->uri->getSite()."static/itemView/".$listing['referenceCode'].'">$'.$listing['price'].' '.$listing['title'].'</a></p>'.
          '<br><p>'.$listing['itemDescription'].'</p></td>'.
          '</tr><br>';
      }
      $this->content.='</tbody></table>';
    }


    public function getContent() {
        return $this->content;
    }
}