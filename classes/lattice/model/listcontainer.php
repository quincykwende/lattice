<?php

/*
 * To change this object_type, choose Tools | Templates
 * and open the object_type in the editor.
 */

/**
 * Description of listcontainer
 *
 * @author deepwinter1
 */
class Lattice_Model_listcontainer extends Model_Object {

  protected $_has_one = array(
    'objecttype' => array('foreign_key'=>'objecttype_id')
  );

  private $_sort_direction = NULL;

  protected $_table_name = 'objects';
  protected $_xml_config = NULL;

  public function __construct($id)
  {
    parent::__construct($id);


  }

  public function get_sort_direction()
  {

    if ( ! $this->_sort_direction)
    {
      $this->_sort_direction = core_lattice::config('objects', sprintf('//list[@name="%s"]', $this->objecttype->objecttypename))->item(0)->getAttribute('sort_direction');   
    }

    return $this->_sort_direction;
  }

  public function get_config()
  {
    if ( ! $this->_xml_config)
    {
      $x_path_lookup = sprintf('//list[@name="%s"]', $this->objecttype->objecttypename);
      $this->_xml_config = core_lattice::config('objects', $x_path_lookup)->item(0);
      if ( ! $this->_xml_config)
      {
        throw new Kohana_Exception('Failed to find x_path config in objects.xml :lookup', array(':lookup' => $x_path_lookup));
      }
    }
    return $this->_xml_config;
  }


  public function add_object($objectTypeName, $data = array(), $lattice = NULL, $rosetta_id = NULL, $language_id = NULL)
  {
    $data['published'] = 1;
    return parent::add_object($objectTypeName, $data, $lattice, $rosetta_id, $language_id);
  }

}
?>
