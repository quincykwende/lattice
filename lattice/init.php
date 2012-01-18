<?

require('lattice/lattice/includes/mysqlfuncs.php');

//check for setup
Lattice_Initializer::check(
	array(
		'rootgraph',
	)
);


Route::set('graph_addChild', '<id>/<action>/<type>', array(
	'action' => 'addChild',
)
		)
		->defaults(
			array(
				'controller' => 'graph',
			));

/*
 * Default path to allow 4 arguments to graph if necessary
 */
Route::set('graph', 'graph/<action>(/<param1>(/<param2>))', array(

	)
)
->defaults(
	array(
		'controller' => 'graph',
	));


Route::set('ajax', 'ajax/(<action>)/(<uri>)', array(
			'controller' => 'ajax',
			'action' => '[A-z]+',
			'uri' => '[A-z\/0-9\-]++',
				)
		)
		->defaults(
				array(
					'controller' => 'ajax',
		));

Route::set('html', '<controller>(/<uri>)', array(
				'controller' => 'html',
					 )
		  )
		  ->defaults(
					 array(
						  'controller' => 'html',
						  'action' => 'html'
		  ));




Route::set('header', 'header(/<id>)')
	->defaults(
		array(
			'controller'=>'header',
			'action'=>'build'
		)
	);

Route::set('footer', 'footer(/<id>)')
	->defaults(
		array(
			'controller'=>'footer',
			'action'=>'build'
		)
	);

/*
Route::set('default4', '(<controller>(/<action>(/<id>(/<thing>))))')
	->defaults(array(
	));
 */

