<?





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


