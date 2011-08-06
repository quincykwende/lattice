<?

/*
 * This class should have it's API changed to respect calling via the Container object Id
 * The container object ID is actually the ID of the list object itself, currently we're addressing
 * lists by family name and parent id, which is actually redundant.
 */


/*
  Class: ListModule_Controller

 */

class Controller_List extends MOP_CMSInterface {
   /*
    *  Variable: object_id
    *  static int the global object id when operating within the CMS submodules get the object id
    *  we could just reference the primaryId attribute of Display as well...
    */

   private static $object_id = NULL;


   /*
     Variable: model
     Main model for items content managed by this class
    */
   protected $model = 'object';

   /*
    * Variable: containerObject
    * The parent object of the lists children
    */
   protected $_containerObject;
   protected $_family;

   
   protected $_listObject;
   protected $_itemView;
   
   
   
    
   /*
     Function:  construct();
     Parameters:
    */

   public function __construct($request, $response) {
      parent::__construct($request, $response);

   }
   
   
   protected function setListObject($listObjectIdOrparentId, $family=null) {

      if ($family != null) {
         $lt = ORM::Factory('objectType')->where('objecttypename', '=', $family)->find();

         $listObject = ORM::Factory('listcontainer')
                 ->where('parentId', '=', $listObjectIdOrparentId)
                 ->where('objecttype_id', '=', $lt->id)
                 ->where('activity', 'IS', NULL)
                 ->find();

         if (!$listObject->loaded()) {
            throw new Kohana_Exception('Did not find list container List object is missing container: :id', array(':id' => $lt->id));
         }

         $this->_listObject = $listObject;
      } else {

         $this->_listObject = ORM::Factory('listcontainer', $listObjectIdOrparentId);
      }
      
      
      
   }
   
   protected function itemView(){
      
     if(!$this->_itemView){
      
     if(!$this->_listObject->loaded()){
        throw new Exception('listObject not set: controller must call setListObject before requesting itemView');
     }
     
      
      $customItemView = $this->_listObject->objecttype->objecttypename . '_item';
      if (Kohana::find_file('views', $customItemView)) {
         $this->_itemView = $customitemView;
      } else {
         $this->_itemView = 'list_item';
      }
      
     }
     
     return $this->_itemView;
     
   
   }

   
   /*
    * Function: action_getList
    * Supports either calling with list object id directly, or with parentId and family 
    * for looking in database and config
    */
   
   public function action_getList($listObjectIdOrparentId, $family = null) {
      
      $this->setListObject($listObjectIdOrparentId, $family);
      

      $view = null;
      if (Kohana::find_file('views', $this->_listObject->objecttype->objecttypename)) {
         $view = new View($this->_listObject->objecttype->objecttypename);
      } else {
         $view = new View('list');
      }

      $listMembers = $this->_listObject->getChildren();

      $html = '';
      foreach ($listMembers as $object) {

         $htmlChunks = mopcms::buildUIHtmlChunksForObject($object);
         $itemt = new View($this->itemView());
         $itemt->uiElements = $htmlChunks;

         $data = array();
         $data['id'] = $object->id;
         $data['object_id'] = $this->_listObject->id;
         $data['instance'] = $this->_listObject->objecttype->templatname;
         $itemt->data = $data;

         $html.=$itemt->render();
      }

      //actually we need to do an absolute path for local config
      $listConfig = $this->_listObject->getConfig();
      $view->label = $listConfig->getAttribute('label');
      $view->class = $listConfig->getAttribute('cssClasses');
      $view->class .= ' allowChildSort-' . $listConfig->getAttribute('allowChildSort');
      $view->class .= ' sortDirection-' . $this->_listObject->getSortDirection();
      $view->items = $html;
      $view->instance = $this->_listObject->objecttype->templatname;
      $view->listObjectId = $this->_listObject->id;


      $this->response->body($view->render());
   }

   //this is the new one
   public function action_savefield($itemid) {
      $object = ORM::Factory($this->model, $itemid);
      $object->$_POST['field'] = $_POST['value'];
      $object->save();
      $this->response->data( array('value' => $object->$_POST['field']) );
   }

   /*
     Function: addItem()
     Adds a list item

     Returns:
     the rendered objectType of the new item
    */

   public function action_addObject($listObjectId, $objectTypeId=null) {
 
      
      $this->setListObject($listObjectId);  //This function should be removed
                                                     //and all functionality simply moved to the model.

           
      $listObject = ORM::Factory('listcontainer', $listObjectId);


      //addable item should be specifid in the addItem call
      if($objectTypeId == null){
   
        $addableObjectTypes = mop::config('objects', sprintf('//list[@family="%s"]/addableObject', $listObject->objecttype->objecttypename));
        if (!$addableObjectTypes->length > 0) {
           throw new Kohana_Exception('No Addable Objects ' .' Count not locate configuration in objects.xml for ' . sprintf('//list[@family="%s"]/addableobject', $this->_family));
        }
        $objectTypeId = $addableObjectTypes->item(0)->getAttribute('objectTypeName');
      } 
 
      $newId = $listObject->addObject($objectTypeId);
      
      $item = Graph::object($newId);
      $htmlChunks = mopcms::buildUIHtmlChunksForObject($item);
      $itemt = new View($this->itemView());
      $itemt->uiElements = $htmlChunks;

      $data = array();
      $data['id'] = $newId;
      $data['object_id'] = $listObjectId;
      ;
      $data['instance'] = $this->_listObject->objecttype->objecttypename;


      $itemt->data = $data;

      $html = $itemt->render();

      $this->response->body($html);
    }

 
}

?>
