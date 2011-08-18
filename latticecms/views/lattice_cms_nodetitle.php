<div class="objectTitle">	
	<div class="<?if($allowTitleEdit):?>ui-Text field-title<?endif;?> grid_7 rows-1">
		<input type='text' class='og title<?=$translationModifier;?> h2' value="<?=$title;?>" />
	</div>
	<?if(Kohana::config('cms.enableSlugEditing')):?>
	<?/*<?=$translationModifier;?> //may not actually need this, since modules only look within themselves... still its good to have*/?>
	<div class="ui-Text field-slug discrete grid_2">
				<input type="text" class="og p" value="<?=$slug;?>" />
	</div>
 	<?endif;?>

	<div class="clear"></div>

</div>
