<?php
/*
 * Class: Controller_Authstatus
 * Reponsible for the 'logged in as' object on the MOP backend objects
 */
class Lattice_Controller_Authstatus extends Controller {

  /*
   * Function: create_index_view()
   * Implements abstract function in base assigning the main view 
   */
  public function action_index()
  {
    $view = new View('auth/logged_in_as');
    if (Auth::instance()->get_user())
    {
      $view->username = Auth::instance()->get_user()->username;
    }
    $this->response->body($view->render());
  }
}
