<div class="objectTitle">	
	<?
	if($allowTitleEdit){
		$elementArray = array( 'type'=>'text', 'name'=>'title', 'isMultiline'=>'false', 'label'=>'Title', 'class'=>'grid_7', 'tag'=>'p', 'labelClass'=>'hidden' );
		echo latticeui::buildUIElement( $elementArray, $title );
	}else{
		$elementArray = array( 'type'=>'text', 'name'=>'title', 'isMultiline'=>'false', 'label'=>'Title', 'class'=>'grid_7 inactive', 'tag'=>'p', 'labelClass'=>'hidden' );
		echo latticeui::buildUIElement( $elementArray, $title );
	}	
	if( Kohana::config('cms.enableSlugEditing') ){
		$elementArray = array( 'type'=>'text', 'name'=>'slug', 'isMultiline'=>'false', 'label'=>'Slug', 'class'=>'grid_4 discrete', 'tag'=>'p', 'labelClass'=>'hidden' );
		echo latticeui::buildUIElement( $elementArray, $slug );
	}
	?>

	<?if( Kohana::config('cms.pageMeta') ):?>
		<a href="#" title='Edit page metadata' class="icon meta pageMeta">Edit Page Meta</a>
	<?endif;?>

	<a class='icon preview' title='Preview this page' href="/<?=$slug;?>">Preview this Page</a>

	<div class="clear"></div>

</div>
