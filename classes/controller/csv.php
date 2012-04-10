<?

Class Controller_CSV extends Controller {

   private $csvOutput = '';
   private $level = 0;
   private $lineNumber = 0;

  public function __construct($request, $response){
   parent::__construct($request, $response);
   if(!latticeutil::checkRoleAccess('superuser')  && PHP_SAPI != 'cli' ){
    die('Only superuser can access builder tool');
   }
  }
   
   public function action_index(){
         $view = new View('csv/index');
        $this->response->body($view->render());
   }
   
   public function action_export($exportFileIdentifier='latticeCsvExport'){
     $this->csvOutput = '';

     $rootObject = Graph::getLatticeRoot();


     $this->level = 0;

     try {
       $this->csvWalkTree($rootObject);
     } catch (Exception $e){
       echo  "Error at line {$this->lineNumber} \n";
       throw $e;
     }

     $filename = $exportFileIdentifier .'.csv';
     $filepath = 'application/export/'.$filename;
     $file = fopen($filepath, 'w');
     fwrite($file, $this->csvOutput);
     // exit;
     header("Pragma: public");
     header("Expires: 0");
     header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
     header("Cache-Control: private",false);
     header("Content-Type: csv");
     header("Content-Disposition: attachment; filename=\"".$filename."\";");
     header("Content-Transfer-Encoding: binary");
     header("Content-Length: ".@filesize($filepath));
     set_time_limit(0);
     @readfile($filepath) or throw new Kohana_Exception("File not found."); 
     exit;
   }

   private function csvWalkTree($parent, $example = false){
     $objects = $parent->getLatticeChildren();

     if ($example || ($this->level > 0 && count($objects))) {
       $childrenLine = array_pad(array('Children'), -1 - $this->level, '');
       $this->csvOutput .= latticeutil::arrayToCsv($childrenLine, ',');
       $this->csvOutput .= "\n";
     }

     foreach($objects as $object){

       $csvView = NULL;
       if($object->objecttype->nodeType != 'container'){
         $csvView = new View_CSV($this->level, $object);
       } else {
         $csvView = new View_CSVContainer($this->level, $object);
       }
       $this->csvOutput .= $csvView->render();
       $this->level++;
       $this->csvWalkTree($object, false);  //false turning off example for now
       //big problem with walking descedent objects since they don't exist

       if($example){
         //And now append one example object of each addable object
         foreach ($object->objecttype->addableObjects as $addableObjectType) {
           $object = Graph::object()->setObjectType($addableObjectType['objectTypeId']);


           $csvView = new View_Csv($this->level, $object);
           $this->csvOutput .= $csvView->render();


         }
       }
       $this->level--;
     }

   }

   public function action_import(){
     $view = new View('csv/uploadform');
     $this->response->body($view->render());
   }

   public function action_importCSVFile($csvFileName=NULL){
     //get the php default Resource Limits
     $max_execution_time = ini_get("max_execution_time");
     $memory_limit = ini_get("memory_limit");


     $max_execution_time = ini_get("max_execution_time");
     $memory_limit = ini_get("memory_limit");

     if($csvFileName == NULL){
       $this->csvFile = fopen($_FILES['upload']['tmp_name'], 'r');
     } else {
       $this->csvFile = fopen($csvFileName, 'r');
     }
     $this->column = 0;

     $this->walkCSVObjects(Graph::getLatticeRoot());

     fclose($this->csvFile);

     try {
       latticecms::regenerateImages();
     } catch(Exception $e){
       print_r($e->getMessage() . $e->getTrace());
     }

     echo 'Done';
   }

   protected function walkCSVObjects($parent){
     $this->advance();
     while($this->line){
       $objectTypeName = $this->line[$this->column];
       if(!$objectTypeName){
         throw new Kohana_Exception("Expecting objectType at column :column, but none found :line",
           array(
             ':column'=>$this->column,
             ':line'=>$this->lineNumber,
           )); 
       }

       //check if this object type is valid for the current objects.xml
       $objectConfig = lattice::config('objects', sprintf('//objectType[@name="%s"]', $objectTypeName));
       if(!$objectConfig->item(0)){
         throw new Kohana_Exception("No object type configured in objects.xml for ".$objectTypeName); 
       }

       //we have an objectType
       $newObjectId = $parent->addObject($objectTypeName);
       $newObject = Graph::object($newObjectId);
       $this->walkCSVElements($newObject);

     }

     echo 'Done';
     flush();
     ob_flush();
   }

   protected function walkCSVElements($object){
     echo "Walking\n";

     if($object->objecttype->nodeType != 'container'){ 
       //get the elements line
       $this->advance();
       //check here for Elements in $this->column +1;
       if(!(isset($this->line[$this->column+1])) || $this->line[$this->column+1] != 'Elements'){
         throw new Kohana_Exception("Didn't find expected Elements line at line ".$this->lineNumber);
       }
     }


     //iterate through any elements
     $this->advance(); 
     $data = array();
     while(isset($this->line[$this->column]) 
       && $this->line[$this->column]=='' 
       && $this->line[$this->column+1]!='' 
       && $this->line[$this->column+1]!='Children'){
         $fieldName = $this->line[$this->column+1]; 
         //echo "Reading $fieldName \n";
         if(isset($this->line[$this->column+2])){
           $value = $this->line[$this->column+2];
         } else {
           $value = null;
         }
         $field = strtok($fieldName, '_');
         $lang = strtok('_');
         if(!isset($data[$lang])){
           $data[$lang] = array();
         }
         $data[$lang][$field] = $value;

         $this->advance();
       }




     //and actually add the data to the objects
     foreach($data as $lang=>$langData){
       $objectToUpdate = $object->getTranslatedObject(Graph::language($lang));
       foreach($langData as $field => $value){

         if($field=='tags'){
           if($value){
             $tags = explode(',',$value); 
             foreach($tags as $tag){
                $objectToUpdate->addTag($tag);
             }
           }
           continue;
         }

         $objectToUpdate->$field = $value;

         if(in_array($field, array('title', 'slug', 'published', 'dateadded'))){
           continue;
         }

         //need to look up field and switch on field type 
         $fieldInfo = lattice::config('objects', sprintf('//objectType[@name="%s"]/elements/*[@name="%s"]',$object->objecttype->objecttypename, $field));
         $fieldInfo = $fieldInfo->item(0);
         if (!$fieldInfo) {
           throw new Kohana_Exception("Bad field in data/objects!\n" . sprintf('//objectType[@name="%s"]/elements/*[@name="%s"]', $object->objecttype->objecttypename, $field));
         }


         //special setup based on field type
         switch ($fieldInfo->tagName) {
         case 'file':
           case 'image':
             $path_parts = pathinfo($value);
             $savename = Model_Object::makeFileSaveName($path_parts['basename']);
             //TODO: Spec and engineer this, import media path needs to be fully workshopped
             //$importMediaPath = Kohana::config('cms.importMediaPath');
             //$imagePath = $_SERVER['DOCUMENT_ROOT']."/".trim($importMediaPath,"/")."/".$value;
             $imagePath = $value;
             if (file_exists($imagePath)) {
               copy($imagePath, Graph::mediapath($savename) . $savename);
               $file = ORM::Factory('file');
               $file->filename = $savename;
               $file->save();
               $objectToUpdate->$field = $file->id;
             } else {
               if($value){
                 echo "file does not exist";
                 //throw new Kohana_Exception( "File does not exist {$value} ");
               }
             }
             break;
           default:
             break;
         }


       }
       $objectToUpdate->save();
     }

     //Check here for Children in $this->column +1
     if(!isset($this->line[$this->column+1]) || $this->line[$this->column+1] != 'Children'){
       echo "No children found, returning from Walk ";//.implode(',', $this->line)."\n";
       return;
     }

     //Iterate through any children
     $this->advance();
     while(isset($this->line[$this->column]) 
       && $this->line[$this->column]=='' 
       && $this->line[$this->column+1]!=''){
         //echo "foudn Child\n";
         //echo $this->column;
         $childObjectTypeName = $this->line[$this->column+1]; 
         $childObjectId = $object->addObject($childObjectTypeName);
         $childObject = Graph::object($childObjectId);
         $this->column++;
         //echo $this->column;
         $this->walkCSVElements($childObject);
         $this->column--;
       }

     echo "Returning from Walk\n";
     //at this point this->line contains the next object to add at this depth level
    
   }

   protected function advance(){
     $this->lineNumber++;
     $this->line = fgetcsv($this->csvFile);
  }

}