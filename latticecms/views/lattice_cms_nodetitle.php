<div class="objectTitle">	

  	<a class='button floatRight' href="#">Preview this Page</a>

	<div class="<?if($allowTitleEdit):?>ui-Text<?endif;?> grid_7" data-ismultiline='false' data-field='title'>
		<input type='text' class='og title<?=$translationModifier;?> h2' value="<?=$title;?>" />
	</div>
	<?if(Kohana::config('cms.enableSlugEditing')):?>
	<div class="ui-Text discrete grid_2" data-ismultiline='false' data-field='slug'>
				<input type="text" class="og p" value="<?=$slug;?>" />
	</div>
 	<?endif;?>
	
	<div class="clear"></div>

</div>

