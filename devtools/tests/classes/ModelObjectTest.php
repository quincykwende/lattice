<?
Class ModelObjectTest extends Kohana_UnitTest_TestCase {

  public static function setUpBeforeClass(){
    $object = Graph::createObject('article', 'object-test-article');
    $object->title = 'The House';
    $object->save();
    $object = Graph::createObject('prevNext', 'model-object-test');
    $object->postDate = '2012-01-01';
    $object->save();
    $object = Graph::createObject('prevNext', 'model-object-test2');
    $object->postDate = '2011-01-01';
    $object->save();
    $object = Graph::createObject('prevNext', 'model-object-test3');
    $object->postDate = '2010-01-01';
    $object->save();

    $testParent = Graph::createObject('article', 'testParent');
    $testParent->addObject('article', array('slug'=>'child1'));
    $testParent->addObject('article', array('slug'=>'child2'));
    $testParent->addObject('article', array('slug'=>'child3'));
  }

  public static function tearDownAfterClass(){
    Graph::object('object-test-article')->delete();
    Graph::object('model-object-test')->delete();
    Graph::object('model-object-test2')->delete();
    Graph::object('model-object-test3')->delete();
    Graph::object('child1')->delete();
    Graph::object('child2')->delete();
    Graph::object('child3')->delete();
    Graph::object('testParent')->delete();
  }

  public function testContentFilterMethodExists(){
    $object = Graph::object();
    $wheres = array();
    $wheres[] = array('title', '=', 'match'); //won't match anything
    $results = $object->contentFilter($wheres);
  }

  public function testContentFilterNoResults(){
    $object = Graph::object();
    $wheres = array();
    $wheres[] = array('title', '=', '2349oiupoupoiuwfpoiaso;dfkaopiuop'); //won't match anything
    $results = $object->contentFilter($wheres)->find_all();
    $this->assertTrue(count($results)==0);
  }

  public function testContentFilterResults(){
    $objects = Graph::object();
    $wheres = array();
    $wheres[] = array('title', 'LIKE', '%House%'); //won't match anything
    $results = $objects->contentFilter($wheres)->find_all();
    $this->assertTrue(count($results)>0);
  }

  public function testNext(){
    //This doesn't always work because the field is not microtime
    $objectQuery = Graph::object()->objectTypeFilter('prevNext');
    $next = $objectQuery->next('dateadded', Graph::object('model-object-test2')->id);
    $this->assertNotNULL($next);
    $this->assertTrue($next->slug == 'model-object-test3');
  }


  public function testPrev(){
    //This doesn't always work because the field is not microtime
    $objectQuery = Graph::object()->objectTypeFilter('prevNext');
    $prev = $objectQuery->prev('dateadded',  Graph::object('model-object-test2')->id);
    $this->assertNotNULL($prev);
    $this->assertTrue($prev->slug == 'model-object-test');
  }

  public function testNextContentColumn(){
    $objectQuery = Graph::object()->objectTypeFilter('prevNext');
    $next = $objectQuery->next('postDate', Graph::object('model-object-test2')->id);
    $this->assertNotNULL($next);
    $this->assertTrue($next->slug == 'model-object-test');
  }


  public function testPrevContentColumn(){
    $objectQuery = Graph::object()->objectTypeFilter('prevNext');
    $prev = $objectQuery->prev('postDate',  Graph::object('model-object-test2')->id);
    $this->assertNotNULL($prev);
    $this->assertTrue($prev->slug == 'model-object-test3');
  }

  public function testChildrenQuery(){
    $items = Graph::object('testParent')
      ->latticeChildrenQuery();
    $items = $items->find_all();
    $this->assertTrue(count($items)>0);
  }

  public function testChildrenQueryActive(){
    $items = Graph::object('testParent')
      ->latticeChildrenQuery()
      ->activeFilter();
    $items = $items->find_all();
    $this->assertTrue(count($items)>0);
  }

  public function testChildrenQueryActiveAndOrder(){
    $items = Graph::object('testParent')
      ->latticeChildrenQuery()
      ->activeFilter()
      ->order_by('objectrelationships.sortorder', 'ASC');
    $items = $items->find_all();
    $this->assertTrue(count($items)>0);
  }
}