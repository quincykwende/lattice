<?if( count($potentialParents) >1 ):?>
<div class="moveWidget">
	<h5>Move</h5>
	<? $opts = array( 'Move this page to...' ) + $potentialParents;?>
	<?=form::select('move', $opts ); ?>
</div>
<?endif;?>