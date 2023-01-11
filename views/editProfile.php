<?php
require_once './lib/abstractView.php';

class EditProfileView extends AbstractView {
    private $content = 'No Profile content';
    private $user;
    private $uri;
    private $session;

    public function __construct($user, $uri, $session) {
      $this->user = $user;
      $this->uri = $uri;
      $this->session = $session;

    }

    public function prepare () {
      // Get data
      $user = $this->session->get('username');
      $getBusinssId = $this->user->getBusinessId($user);
      $userResult = $this->user->getUserDetails($user);
      $businessResult = $this->user->getUserBusiness($getBusinssId);

      // Content
      $this->content = '<div style="width:1000px">'.
        '<form method="POST" action="##site##static/profile/'.$user.'/update" enctype="multipart/form-data">'.
            '<div class="col-sm-5 row g-1">'.
                '<h2>User Details</h2>'.
                '<label for="ownerfname" class="form-label">First Name</label>'.
                '<input type="text" class="form-control" name="ownerfname" value="'.$userResult[0]['firstName'].'"><br>'.
                '<label for="ownerlname" class="form-label">Last Name</label>'.
                '<input type="text" class="form-control" name="ownerlname" value="'.$userResult[0]['lastName'].'"><br>'.
            '</div>'.
            '<br>'.
            '<div class="col-sm-5 row g-1">'.
                '<h2>Business Details</h2>'.
                '<label for="businessname" class="form-label">Business Name</label>'.
                '<input type="text" class="form-control" name="businessname" value="'.$businessResult[0]['name'].'"><br>'.
                '<label for="locationbased" class="form-label">Location Based</label>'.
                '<input type="text" class="form-control" name="locationbased" value="'.$businessResult[0]['locationBased'].'"><br>'.
                '<label for="email" class="form-label">Email</label>'.
                '<input type="email" class="form-control" name="email" value="'.$businessResult[0]['email'].'"></input><br>'.
                '<label for="mobile" class="form-label">Mobile</label>'.
                '<input type="tel" class="form-control" name="mobile" value="'.$businessResult[0]['mobile'].'"></input><br>'.
                '<label for="logo" class="form-label">Business Icon (upload if new)</label><br>'.
                '<input type="file" class="form-control" name="logo"><br>'.
            '</div>'.
            '<br>'.
            '<input type="submit" value="update" class="btn btn-primary" name="update"/>'.
        '</form>';
    }

    public function getContent() {
        return $this->content;
    }
}