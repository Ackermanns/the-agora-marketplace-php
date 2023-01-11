<?php
require_once './lib/abstractView.php';

class MarketplaceView extends AbstractView {
    private $content = '';
    private $listings;
    private $uri;
    private $session;

    public function __construct($listings, $uri, $session) {
      $this->listings = $listings;
      $this->uri = $uri;
      $this->session = $session;
    }

    public function prepare () {

      // Marketplace data
      $result = $this->listings->getMarketplaceListings();
      // Search terms
      $this->content = '<div class="form-group row">'.
      '<div class="col-sm-4">'.
      '<form method="POST" action="##site##static/marketplace/add">'.
      '<div class="input-group">'.
      '<input type="text" name="search" class="form-control rounded" placeholder="Search" />'.
      '<button type="submit" class="btn btn-primary">search</button>'.
      '</div>'.
      '</form>'.
      '</div>'.
      '<div class="col-sm-1">'.
      '<form method="POST" action="##site##static/marketplace/clear">'.
      '<button class="btn btn-primary" type="submit">Clear</button>'.
      '</form>'.
      '</div>'.
      '</div>';
      
      

      
      // Show searched terms

      $search = $this->session->get('searchTerms');
      $this->content .= "<p>Searched terms: ";
      if (count($search) !== 0) {
        foreach ($search as $s) {
          $this->content .= "{$s}, ";
        }
        $this->content .= '</p>';
      }

      // Marketplace listing content
      $this->content .= '<table class="table table-striped table-dark\"><tbody>';
     
      foreach ($result as $item) {
        $viewItemLink = $this->uri->getSite()."static/itemView/".$item['referenceCode'];
        $imgLink = $this->uri->getSite().'images/listings/'.$item['itemImage'];
        $this->content.='<tr>'.
          '<td class="col-3"><img src="'.$imgLink.'" width="250" height="auto"/></td>'.
          '<td><p class="display-6"><a href="'.$viewItemLink.'">$'.$item['price'].' '.$item['title'].'</a></p>'.
          '<br><p>'.$item['itemDescription'].'</p>'.
          '<br><p>'.$item['hashtags'].'</p></td>'.
          '</tr><br>';
      }
      $this->content.='</tbody></table>';
    }

    public function getContent() {
        return $this->content;
    }
}