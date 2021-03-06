<?

$config['resources']['librarycss'] = array(
	'modules/lattice/resources/thirdparty/960Grid/reset.css',
	'modules/lattice/resources/thirdparty/960Grid/960.css',
	'modules/lattice/resources/thirdparty/arian/datepicker/Source/datepicker.css',

	// for autocomplete
	'modules/lattice/resources/thirdparty/mootools/MooComplete.css'
);
$config['resources']['libraryjs'] = array(

	'modules/lattice/resources/thirdparty/mootools/mootools.js',
	'modules/lattice/resources/thirdparty/mootools/mootools-more.js',
	
	// for live search of objects
	'modules/lattice/resources/thirdparty/live_list_search.js',

	// for autocomplete
	'modules/lattice/resources/thirdparty/mootools/MooComplete.js',
	
	// these are required by LatticeUI
	'modules/lattice/resources/thirdparty/digitarald/fancyupload/Swiff.Uploader.js',
	'modules/lattice/resources/thirdparty/arian/datepicker/Source/Locale.en-US.DatePicker.js',
	'modules/lattice/resources/thirdparty/arian/datepicker/Source/Picker.js',
	'modules/lattice/resources/thirdparty/arian/datepicker/Source/Picker.Attach.js',
	'modules/lattice/resources/thirdparty/arian/datepicker/Source/Picker.Date.js',
	'modules/lattice/resources/thirdparty/arian/datepicker/Source/Picker.Date.Range.js',

	'modules/lattice/resources/js/LatticeCore.js',
	'modules/lattice/resources/js/LatticeModules.js',
	'modules/lattice/resources/js/LatticeUI.js',

	'modules/lattice/resources/js/navigationDataSourceInterface.js',
);

$config['resources']['js'] = array(
	'modules/lattice/resources/js/list.js',
	'modules/lattice/resources/js/associator.js',
);


$config['defaultsettings']['editable_title'] = true;
// - - if set all titles editable

$config['new_object_placement'] = 'bottom';

$config['uiresizes'] =  array(
	'uithumb' => array(
		'width'=>240,
		'height'=>120,
		'prefix' => 'uithumb',
		'force_dimension'=>'width',
		'crop'=>true,
    'aspect_follows_orientation'=>false
	)
);

$config['navigation_request'] = 'navigation';


$config['stagingmediapath'] = 'staging/application/media/';
$config['basemediapath'] = 'application/media/';

$config['enable_slug_editing'] = false;

$config['base_name'] = 'modules_cms';
$config['localization'] = FALSE;




return $config;
