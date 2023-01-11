<?php
require_once './lib/abstractView.php';

class ProfileView extends AbstractView {
    private $content = 'No Profile content';
    private $db;
    private $uri;
    private $session;
    private $user;

    public function __construct($database, $uri, $session, $user) {
      $this->db = $database;
      $this->uri = $uri;
      $this->session = $session;
      $this->user = $user;
    }

    public function prepare () {
      // Persons account to view
      $remainder = explode('/', $this->uri->getRemainingParts());
      $viewUser = $remainder[1];
      $Currentuser = $this->session->get('username');
      $getBusinssId = $this->user->getBusinessId($viewUser);
      $userResult = $this->user->getUserDetails($viewUser);
      $businessResult = $this->user->getUserBusiness($getBusinssId);
      $contactsResult = $this->user->getUserContacts();

      // Business Image logo
      if ($businessResult[0]['logo'] === "") {
        $imgLink = $this->uri->getSite().'images/businesses/placeholder.jpg';
      }
      else {
        $imgLink = $this->uri->getSite().'images/businesses/'.$businessResult[0]['logo'];
      }
      
      // Account and Business details
      $this->content .= '<h2>Account details - '.$viewUser.'</h2>'.
      '<p>First Name: '.$userResult[0]['firstName'].'</p>'.
      '<p>Last Name: '.$userResult[0]['lastName'].'</p>'.
      '<h2>Business details - '.$businessResult[0]['name'].'</h2>'.
      '<img src="'.$imgLink.'" width="250" height="auto"/>'.
      '<p>Location Based: '.$businessResult[0]['locationBased'].'</p>'.
      '<p>Email: '.$businessResult[0]['email'].'</p>'.
      '<p>Mobile: '.$businessResult[0]['mobile'].'</p>';
      
      // Add contact
      if ($viewUser === $Currentuser) {
        $this->content .= '<h2>Add contact</h2>'.
        '<div class="form-group row">'.
        '<div class="col-sm-4">'.
        '<form method="POST" action="##site##static/profile/'.$Currentuser.'/add">'.
        '<div class="input-group">'.
        '<input type="text" name="addUsername" class="form-control rounded" placeholder="Search" />'.
        '<button type="submit" class="btn btn-primary">search</button>'.
        '</div>'.
        '</form>'.
        '</div>';

        // View Contacts
        $this->content .= '<br><br><br><h2>My Contacts</h2>';
        if (count($contactsResult) !== 0) {
          $this->content .= '<table class="table table-striped table-dark\"><tbody>';
          foreach ($contactsResult as $listing) {
            $this->content.='<tr>'.
              '<td><a href="##site##static/profile/'.$listing['username'].'/view">'.$listing['username'].'</a></td>'.
              '<br>';
          }
          $this->content.='</tbody></table><br>';
        }
      }

      // Edit profile button
      if ($viewUser === $Currentuser) {
        $this->content .= '<h2>Edit Profile</h2>'.
        '<form method="POST" action="##site##static/profile/'.$Currentuser.'/edit">'.
        '<button type="submit"><img src="##site##images/platform/edit.png" style="width: 12px; height: 12px;"/>Edit</button>'.
        '</form>';
      }
    }

    public function getContent() {
        return $this->content;
    }
}

?>