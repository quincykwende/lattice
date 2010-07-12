<?
require('/home/deepwinter/dev/deepwinter/yaml/lib/sfYaml.php');
require('/home/deepwinter/dev/deepwinter/yaml/lib/sfYamlParser.php');
/*
 * Class: Site_Controller
 * Responsible for handing default behaviour of CMS driven sites,
 * using slugs or ids to navigate between pages.  Works hand in
 * hand with the slugs hook
 */
Class Site_Controller extends Controller{

	/*
	 * Variable: $content
	 * Holds the content for a page
	 */
	protected $content = array();

	/*
	 * Function: index
	 * Wrapper to page that uses the controller as the slug
	 */
	public function index(){
		$this->page(substr(get_class($this), 0, -11));
	}

	/*
	 * Function: page($pageidorslug)
	 * By default called after a rewrite of routing by slugs hooks, gets all content
	 * for an object and loads
	 * Parameters:
	 * $pageidorslug - the id or slug of the object to display, null is allowed but causes exception
	 * Returns: nothing, renders full webpage to browser or sents html if AJAX request
	 */
	public function page($pageidorslug=null) {

		$page = ORM::Factory('page', $pageidorslug);
		//some access control
		if(!$page->loaded || $page->published==false || $page->activity!=null){
			throw new Kohana_User_Exception('Page not availabled', 'The page with identifier '.$id.' is does not exist or is not available');
		}

		//keep this first line for backwards compatibility
		$this->content = array_merge($this->content, $page->getPageContent());
		//but this is the real deal
		$this->content['main'] = $page->getPageContent();

		//look for the template, if it's not there just print out all the data raw
		$view = 'site/'.$page->template->templatename;
		if(Kohana::find_file('views', $view)){
			$this->template = new View( 'site/'.$page->template->templatename);
		} else {
			$this->template = new View( 'site/default');
		}


		$yaml = new sfYamlParser();
		try {
			$configArray = $yaml->parse(file_get_contents('application/config/frontend.yaml'));
		}
		catch (InvalidArgumentException $e) {
			// an error occurred during parsing
			echo "Unable to parse the YAML string: ".$e->getMessage();
			flush();
			ob_flush();
			exit;
		}
		$newConfig = array('views'=>array());
		foreach($configArray['views'] as $view){
			$newConfig['views'][$view['view']] = $view;	
		}
		$configArray = $newConfig;
		
		if(isset($configArray['views'][$page->template->templatename])){
			foreach($configArray['views'][$page->template->templatename]['extendeddata'] as $edata){
				
				$objects = ORM::Factory('page');
				if(isset($edata['parent'])){
					$parent = ORM::Factory('page', $edata['parent']);
					$objects->where('parentid', $parent->id);	
				}
				$objects = $objects->find_all();

				$this->content[$edata['label']] = array();
				foreach($objects as $object){
					$this->content[$edata['label']][] = $object->getContent();
				}

			}
		}

		$this->template->content = $this->content;

		if($this->responseFormat=='AJAX'){
			return $this->template->render();
		} else {
			$customDisplayController = null;
			if( Kohana::find_file('config', $page->slug)){
				if( Kohana::config($page->slug.'.displaycontroller')){
					$customDisplayController = Kohana::config($page->slug.'.displaycontroller');
				}
			}
			$this->toWebpage($customDisplayController);
		}
	}



	/*
	 * Function: __buildPageContent($content, $page)
	 * Deprecated function used to build content array for a given page.
	 * The correct way to do this now is to call getPageContent on a loaded Page model
	 * */
	protected function __buildPageContent(& $content, & $page){
		if (is_array($content))
			$content = array_merge($content, $page->getPageContent());
		else
			$content = $page->getPageContent();
		return;
	}

	/*
	 * Function: getChildrenContent($pageid, & $content)
	 * Deprecated function to load content of children.  Use $page->getPublishedChildren() instead
	 */
	public function getChildrenContent($pageid, & $content){
		$page = ORM::Factory('page', $pageid);
		$pages = $page->getPublishedChildren;

		$content = array();
		foreach($pages as $page){
			$pagecontent = $page->getPageContent();
			$content[$page->slug] = $pagecontent;
		}
	}


	/*
	 * Function: getContentsByPageId($pageid)
	 * Deprecated function used to build content array for a given page.
	 * The correct way to do this now is to call getPageContent on a loaded Page model
	 */
	protected function getContentsByPageId($pageid){
		return ORM::Factory('page', $pageid)->getPageContent();
	}

	/*
	 * Function: getContentsByPageIdentifier($pageid)
	 * Deprecated function used to build content array for a given page.
	 * The correct way to do this now is to call getPageContent on a loaded Page model
	 */
	protected function getContentsByPageIdentifier($identifier){
		return ORM::Factory('page', $pageid)->getPageContent();
	}

	/*
	 * Function: getListData($instance, $pagid=null)
	 * Deprecated function used to build content array for a given list.
	 * The correct way to do this now is to call getListData a loaded Page model
	 * Soon we'll get away from the page vs. list content and this function will
	 * completely disappear
	 */
	protected function getListData($instance, $pageid){
			return ORM::Factory('page', $pageid)->getListData();
	}

}
