<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Auth module demo controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * $Id: auth_demo.php 3267 2008-08-06 03:44:02Z Shadowhand $
 *
 * @package    MopAuth
 * @author     Deepwinter
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Controller_Auth extends Controller_Layout {

	protected $_actionsThatGetLayout = array(
                                         'index',
                                         'login',
                                         'logout',
                                         'noaccess',
                                         'forgot',
       );


	// Use the default Kohana template
	public $defaulttemplate = 'auth/template';

	public $message = '';

	public function __construct($request, $response){
		parent::__construct($request, $response);

	}

	public function action_index()
	{
		$this->action_login();
	}

	/*
	 * Currently not supporting public creation of users
	public function action_create()
	{
		$this->view->title = 'Create User';

		$form = new Forge('auth/create');
		$form->input('email')->label(TRUE)->rules('required|length[4,32]|valid_email');
		$form->input('username')->label(TRUE)->rules('required|length[4,32]');
		$form->password('password')->label(TRUE)->rules('required|length[5,40]');
		$form->submit('Create New User');

		if ($form->validate())
		{
			// Create new user
			$user = ORM::factory('user');

			if ( ! $user->username_exists($form->username->value))
			{
				foreach ($form->as_array() as $key => $val)
				{
					// Set user data
					$user->$key = $val;
				}

				if ($user->save() AND $user->add(ORM::factory('role', 'login')))
				{
					Auth::instance()->login($user, $form->password->value);

					// Redirect to the login page
					url::redirect('auth/login');
				} 

			} else {
			}
		}

		$this->view = new View('auth/create');
		$this->view->content = $form->render();
	}
	 */

	public function action_login($redirect = null)
	{

		if (Auth::instance()->logged_in())
		{
			$view = new View('auth/logout');
			if($this->message){
				$view->title = $this->message;
			} else if($redirect = Kohana::config('auth.redirect')){
				url::redirect($redirect);
			} else {
				$view->title = 'User Logout';
			}

			$this->response->body($view->render());

		}
		else
		{

			$error = null;

			/*
			 * Switch to validate library
			 $form->input('username')->label(TRUE)->rules('required|length[4,32]');
			$form->password('password')->label(TRUE)->rules('required|length[5,40]');
			 */

			$formValues = $_POST;

			if (isset($formValues['submit']) )
			{
         	// Load the user
           if (Auth::instance()->login($formValues['username'], $formValues['password']))
				{
					// Login successful, redirect
					if($form->redirect){
						url::redirect($form->redirect->value);
					} else if($redirect = Kohana::config('auth.redirect')){
						url::redirect($redirect);
					} else {
						url::redirect('auth/login');
					}
               return;
				}
				else
				{
          		$error = 'Invalid username or password.';
				}
			}

echo 'hello';

			$view = new View('auth/login');

			if($redirect == 'resetPasswordSuccess'){
				$view->message = Kohana::lang('auth.resetPasswordSuccess');
				$redirect = null;
			} else if($error){
				$view->message = $error;
			}
			$view->title = 'User Login';


			$view->redirect = $redirect;
echo 'hello';
			$this->response->body($view->render());

			}
	}

	public function action_logout()
	{
		// Force a complete logout
		Auth::instance()->logout(TRUE);

		// Redirect back to the login page
		url::redirect('auth/login');
	}

	public function action_noaccess($controller = NULL){
		$this->message = 'You do not have access to the requested page';
		$this->login($controller);
	}

	public function action_forgot(){
		if(isset($_POST['email'])){
			$user = ORM::Factory('user')->where('email', $_POST['email'])->find();
			if($user->loaded){
				$password = $this->randomPassword();
				$user->password = $password;
				$user->save();
				$body = Kohana::lang('auth.forgotPasswordEmailBody');
				$body = str_replace('___MOP___username___MOP___', $user->username, $body);
				$body = str_replace('___MOP___password___MOP___', $password, $body);
				mail($user->email, Kohana::lang('auth.forgotPasswordEmailSubject'), $body);
				url::redirect('auth/login/resetPasswordSuccess');
				
			} else {
				$this->view = new View('auth/forgot');
				$this->view->message = Kohana::lang('auth.resetPasswordFailed');

			}
		} else {
			$this->view = new View('auth/forgot');
		}
	}

	public function randomPassword(){
		$password_length = 9;

		function make_seed() {
			list($usec, $sec) = explode(' ', microtime());
			return (float) $sec + ((float) $usec * 100000);
		}

		srand(make_seed());

		$alfa = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
		$token = "";
		for($i = 0; $i < $password_length; $i ++) {
			$token .= $alfa[rand(0, strlen($alfa)-1)];
		}    
		return $token;


	}

} // End Auth Controller
