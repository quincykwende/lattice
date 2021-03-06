<?php

/*
 * Class: cms_ui
 * Helper class which generates all the cms_ui form elements
 * Functions in this class need to be stardardized and have their parameters cleaned up
 */
/* @package Lattice */

Class Lattice_Cms_Ui{

  private static $unique = 1;

  /*
   * Function: build_ui_element
   * Builds a UI element from the cms_ui views directory
   * $element -  array of key value pairs passed to the object_type, including 'type' key which indicates the object_type to use
   * $fieldvalue - the value to display
   * Example: build_text_element(array('type'=>'text', 'name'=>'fieldname', 'class'=>'class_name'), {Value})
   */
  public static function build_ui_element($element, $fieldvalue=NULL)
  {
    $view = 'ui/'.$element['type'];

    // allow files to be passed either by id or as already quireid objects
    if ( in_array($element['type'], array('file', 'image')))
    {

      if ( ! is_object($fieldvalue) )
      {
        $fieldvalue = ORM::Factory('file', $fieldvalue);
      }

      if ($fieldvalue->_loaded)
      {
        $fieldvalue = $fieldvalue->as_array();
      } else {
        $fieldvalue = NULL;
      }
    }

    // provide a unique id always
    $microtime = str_replace(array(' ', '.'), '', microtime());
    if (isset($element['name']))
    {
      // 	$object_type->id =$element['name'].str_replace(array(' ', '.'), '', microtime());
      $id =$element['name'].lattice_cms_ui::$unique++.$microtime;
    } else {
      $id ='name'.lattice_cms_ui::$unique++.$microtime;
    }
    $element['id'] = $id;

    switch($element['type'])
    {

    case 'image':
      $ext = substr(strrchr($fieldvalue['filename'], '.'), 1);
      switch($ext)
      {
      case 'tif':
      case 'tiff':
      case 'TIF':
      case 'TIFF':
        $thumb_src = 'uithumb_'.$fieldvalue['filename'].'_converted.jpg';
        break;
      default:
        $thumb_src = 'uithumb_'.$fieldvalue['filename'];
        break;
      }

      $site_path = '';

      if (Kohana::config('lattice.staging'))
      {
        $site_path = 'staging/';
      }
      if (file_exists($site_path.'application/media/'.$thumb_src))
      {
        $size = getimagesize($site_path.'application/media/'.$thumb_src);	
        $fieldvalue['width'] = $size[0];
        $fieldvalue['height'] = $size[1];
      } else {
        $fieldvalue['width'] = 0;
        $fieldvalue['height'] = 0;
      }
      $fieldvalue['thumb_src']=$thumb_src;

    case 'file':
      if ( ! isset($element['maxlength']) OR !$element['maxlength'])
      {
        $element['maxlength'] = 1523712; // 12 Mega_bytes 
      }
      break;

    case 'date_range':
      if ( ! isset($element['empty']))
      {
        if ( ! isset($element['start_date']) OR strlen($element['start_date'])==0)
        {
          $element['start_date'] = date('Y/m/d');
        }
        if ( ! isset($element['end_date']) OR strlen($element['end_date'])==0)
        {
          $element['end_date'] = date('Y/m/d');
        }
      } else {
        $element['start_date'] = '';
        $element['end_date'] = '';
      }
      break;

    case 'date':
      $fieldvalue = explode(' ',$fieldvalue);
      $fieldvalue = $fieldvalue[0];

      $fieldvalue = ($fieldvalue)? date('m/d/Y', strtotime($fieldvalue)) : date('m/d/Y', time());
      break;

    case 'radio_group':
      $element['radioname'] = $id; 
      break;

    case 'multi_select':
      if (isset($element['object']))
      {
        $object = Kohana::config('cms.object_types.'.$element['object']);
        $element['options'] = array();
        foreach ($object as $field)
        {
          if ($field['type'] == 'checkbox')
          {
            $element['options'][$field['name']] = $field['label'];
          }
        }
      }	
      if ($fieldvalue)
      {
        $prep_field_value = array();
        foreach ($fieldvalue as $name => $selected)
        {
          if ($selected)
          {
            $prep_field_value[] = $name;
          }
        }
        $fieldvalue = $prep_field_value;
      }
      break;
      
    case 'item':
		$element['item_name'] =  $fieldvalue;
		break;
    }

    if ( ! isset($element['class']))
    { $element['class'] = NULL; }

    if ($paths = Kohana::find_file('views', $view))
    {
      $object_type = new View($view);
      $object_type->id = $id;
      $object_type->class = NULL;
      foreach ($element as $key=>$value)
      {
        $object_type->$key = $value;
      }
      $object_type->value = $fieldvalue;
      return $object_type->render();
    } else {
      throw new Kohana_Exception('bad ui element request'. ' view: '.$view.' not found');
      return FALSE;
    }
  }

  public static function Input( $field, $class, $tag, $field_value, $label=NULL, $size=32 )
  {
    $element_array = array( 'type'=>'input', 'name'=>$field, 'label'=>$label, 'class'=>$class, 'tag'=>$tag, "size"=>$size );
    return cms_ui::build_ui_element( $element_array, $field_value);
  }

  public static function text( $field, $class, $tag, $field_value, $label=NULL, $label_class=NULL )
  {
    $element_array = array( 'type'=>'text', 'name'=>$field, 'label'=>$label, 'class'=>$class, 'tag'=>$tag, "label_class"=>$label_class );
    return cms_ui::build_ui_element( $element_array, $field_value);
  }

  public static function password( $field, $class, $tag, $field_value, $label=NULL, $label_class=NULL )
  {
    $element_array = array( 'type'=>'password', 'name'=>$field, 'label'=>$label, 'class'=>$class, 'tag'=>$tag, "label_class"=>$label_class );
    return cms_ui::build_ui_element( $element_array, $field_value);		
  }

  public static function radio_group( $field, $class, $radios, $field_value, $label=NULL, $label_class=NULL )
  {
    $microtime = str_replace(array(' ', '.'), '', microtime());
    $name =$field.lattice_cms_ui::$unique++.$microtime;
    $element_array = array( 'type'=>'radio_group', 'radioname'=>$name, 'class'=>$class, 'grouplabel'=>$label, 'name'=>$field, 'radios'=> $radios, "label_class"=>$label_class );
    return cms_ui::build_ui_element( $element_array, $field_value );
  }

  public static function checkbox( $field, $checkboxvalue, $value, $label)
  {
    return cms_ui::build_ui_element( array('type'=>'checkbox', 'name'=>$field, 'checkboxvalue'=>$checkboxvalue, 'label'=>$label, 'class'=>'checkbox'), $value);
  }

  public static function file($field, $extensions, $maxlength, $current_file=NULL )
  {
    return cms_ui::build_ui_element( array('type'=>'file', 'name'=>$field, 'extensions'=>$extensions, 'maxlength'=>$maxlength,  ), $current_file );
  }

  public static function fieldmap($values, $options)
  {
    return cms_ui::build_ui_element(array('type'=>'fieldmap', 'values'=>$values, 'options'=>$options) );
  }

  //  0ak - revisit this function.  Needed?
  public static function pulldown ( $field, $class, $options, $field_value, $label=NULL, $label_class=NULL )
  {
    $element_array = array( 'type'=>'pulldown',  'class'=>$class, 'label'=>$label, 'name'=>$field, 'options'=> $options, "label_class"=>$label_class );
    return cms_ui::build_ui_element( $element_array, $field_value );
  }


  public static function tags($current_tags)
  {
    $view = new View('ui/tags');
    $view->tags = $current_tags;
    return $view->render();
  }
  
  // used to display the item type
  public static function item($item_name)
  {
	 return View::factory('ui/item')->set('item_name', $item_name);
  }
  
  // render search field
  public static function search($search_field = null)
  {
	 return View::factory('ui/search')->set('search_field', $search_field);
  }
}

