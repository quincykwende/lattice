<?
/**
 * Class: CMS_Intergace_Controller
 * The main CMS class, handling add, delete, and retrieval of pages
 * @author Matthew Shultz
 * @version 1.0
 * @package Kororor
 */


class MOP_CMSInterface extends Controller_Layout {


	/*
		Function: __constructor
		Loads subModules to build from config	
	*/
	public function __construct($request, $response){
		parent::__construct($request, $response);
	}

	/*
	 * Function:  saveFile($pageid)
	 * Function called on file upload
	 * Parameters:
	 * pageid  - the page id of the object currently being edited
	 * $_POST['field'] - the content table field for the file
	 * $_FILES[{fieldname}] - the file being uploaded
	 * Returns:  array(
		 'id'=>$file->id,
		 'src'=>$this->basemediapath.$savename,
		 'filename'=>$savename,
		 'ext'=>$ext,
		 'result'=>'success',
	 );
	 */
	public function action_saveFile($pageid){

		$file = mopcms::saveHttpPostFile($pageid, $_POST['field'], $_FILES[$_POST['field']]);
		$result = array(
			'id'=>$file->id,
			'src'=>$file->original->fullpath,
			'filename'=>$file->filename,
			'ext'=>$file->ext,
			'result'=>'success',
		);	
		
		//if it's an image
		$thumbSrc = null;
		if($file->uithumb->filename){
			if(file_exists(mopcms::mediapath().$file->uithumb->filename)){
				$resultpath = mopcms::mediapath().$file->uithumb->filename;
				$thumbSrc = Kohana::config('cms.basemediapath').$file->uithumb->filename;
			}
		}
		if($thumbSrc){
			$size = getimagesize($resultpath);
			$result['width'] = $size[0];
			$result['height'] = $size[1];
			$result['thumbSrc']= $thumbSrc;
		}


		return $result;

	}

	/*
	*
	* Function: savefield()
	* Saves data to a field via ajax.  Call this using /cms/ajax/save/{pageid}/
	* Parameters:
	* $id - the id of the object currently being edited
	* $_POST['field'] - the content table field being edited
	* $_POST['value'] - the value to save
	* Returns: array('value'=>{value})
	 */
	public function action_savefield($id){
		$page = ORM::Factory('page', $id);

      
		if($_POST['field']=='slug'){
			$page->slug = mopcms::createSlug($_POST['value'], $page->id);
			$page->decoupleSlugTitle = 1;
			$page->save();
			$this->response->data( array('value'=>$page->slug) );
			return;
		} else if($_POST['field']=='title'){
			if(!$page->decoupleSlugTitle){
				$page->slug = mopcms::createSlug($_POST['value'], $page->id);
			}
			$page->save();
			$page->contenttable->title = mopcms::convertNewlines($_POST['value']);
			$page->contenttable->save();
			$page = ORM::Factory('page')->where('id', '=', $id)->find();
			$this->response->data( array('value'=>$page->contenttable->$_POST['field'], 'slug'=>$page->slug) );
         return;
		}
		else if(in_array($_POST['field'], array('dateadded'))){
			$page->$_POST['field'] = $_POST['value'];
			$page->save();
		} else if($_POST['field']) {
			$xpath = sprintf('//template[@name="%s"]/elements/*[@field="%s"]',
				$page->template->templatename,
				$_POST['field']);
			$fieldInfo = mop::config('objects', $xpath)->item(0);
			if(!$fieldInfo){
				throw new Kohana_Exception('Invalid field for template, using XPath : :xpath', array(':xpath'=>$xpath));
			}

			switch($fieldInfo->getAttribute('type')){
			case 'multiSelect':
				$object = ORM::Factory('page', $_POST['field']);
				if(!$object->loaded){
					$object->template_id = ORM::Factory('template', $lookup[$_POST['field']]['object'])->id;
					$object->save();
					$page->contenttable->$_POST['field'] = $object->id;
					$page->contenttable->save();
				}
				$options = array();
				foreach(mop::config('objects', sprintf('/template[@name="%s"]/element', $object->template->templatename)) as $field){
					if($field->getAttribute('type') == 'checkbox'){
						$options[] = $field['field'];
					}
				}
				foreach($options as $field){
					$object->contenttable->$field  = 0;
				}

				foreach($_POST['value'] as $value){
					$object->contenttable->$value = 1;
				}
				$object->contenttable->save();
				break;	
			default:
				$page->contenttable->$_POST['field'] = mopcms::convertNewlines($_POST['value']);
				$page->contenttable->save();
				break;
			}
		} else {
			throw new Kohana_Exception('Invalid POST Arguments, POST must contain field and value parameters');
		}

		$page = ORM::Factory('page')->where('id', '=', $id)->find();
      $value = $page->contenttable->$_POST['field'];
      $this->response->data(array('value'=>$value));
	}



	/*
		Function: togglePublish
		Toggles published / unpublished status via ajax. Call as cms/ajax/togglePublish/{id}/
		Parameters:
		id - the id to toggle
		Returns: Published status (0 or 1)
		*/
		public function action_togglePublish($id){
			$page = ORM::Factory('page')->where('id', '=', $id)->find();
			if($page->published==0){
				$page->published = 1;
			} else {
				$page->published = 0;
			}
			$page->save();

			$this->response->data( array('published' => $page->published) );
		}

		/*
		Function: saveSortOrder
		Saves sort order of some ids
		Parameters:
		$_POST['sortorder'] - array of page ids in their new sort order
		*/
		public function action_saveSortOrder(){
			$order = explode(',', $_POST['sortorder']);

			for($i=0; $i<count($order); $i++){
				if(!is_numeric($order[$i])){
					throw new Kohana_User_Exception('bad sortorder string', 'bad sortorder string');
				}
				$page = ORM::factory('page', $order[$i]);
				$page->sortorder = $i+1;
				$page->save();
			}

			$this->response->data( array('saved'=>true));
		}

		/*
		 Function: delete
		 deletes a page/category and all categories and leaves underneath
		 Returns: returns html for undelete pane 
		*/
		public function action_removeObject($id){
			$this->cascade_delete($id);

			$view = new View('mop_cms_undelete');
			$view->id=$id;
         $this->response->body($view->render());
         $this->response->data(array('deleted'=>true));

		}


		/*
		 Function: undelete
		 Undeletes a page/category and all categories and leaves underneath

		 Returns: 1;
		*/
		public function action_undelete($id) {
			$this->cascade_undelete($id);
			//should return something about failure...
			$this->response->data( array('undeleted'=>true) );

		}

	
	/*
	 * Function: cascade_delete($id)
	 * Private utility to recursively delete and object and everything beneath a node
	 * Parameters:
	 * id - the id to delete as well as everything beneath it.
	 * Returns: nothing 
	 */
	protected function cascade_delete($id){
		$page = ORM::Factory('page')->where('id', '=', $id)->find();
		$page->activity = 'D';
		$page->sortorder = 0;
		$page->slug = DB::expr('null');
		$page->save();
		$page->contenttable->activity = 'D';
		$page->contenttable->save();

		$children = ORM::Factory('page');
		$children->where('parentid', '=',$id);
		$iChildren = $children->find_all();
		foreach($iChildren as $child){
			$this->cascade_delete($child->id);
		}
	}

	/*
	 * Function: cascade_undelete($id)
	 * Private utility to recursively undelete and object and everything beneath a node
	 * Parameters:
	 * id - the id to undelete as well as everything beneath it.
	 * Returns: Nothing
	 */
	protected function cascade_undelete($page_id){
		$page = ORM::Factory('page')->where('id', '=', $id)->find();
		$page->activity = new Database_Expression(null);
		$page->slug = mopcms::createSlug($page->contenttable->title, $page->id);
		$page->save();
		$page->contenttable->activity = new Database_Expression(null);
		$page->contenttable->save();

		$children = ORM::Factory('page');
		$children->where('parentid','=',$id);
		$iChildren = $children->find_all();
		foreach($iChildren as $child){
			$this->cascade_undelete($child->id);
		}

	}



}

?>
