<?php


// named routes
$map['root'] = array (
	'controller' => 'admin'
);
$map['login'] = array (
	'controller' => 'admin',
	'action' => 'login'
);
$map['logout'] = array (
	'controller' => 'admin',
	'action' => 'logout'
);

// defining resources and nested resources.
$map['resources'] = array (
	'terms',
	'options',
	'links',
	'posts' => array (
		'add' => array (
			'controller' => 'comments',
			'action' => 'create_comment'
		)
	), // named and other narmal route in resources.
	'users' => array (
		'terms',
		'posts'
	)
); // nested resources

// regular expressions and parameters for requirements routes.
$map['/:year/:month/:day'] = array (
	'controller' => 'posts',
	'action' => 'find_by_date',
	'year' => '/^\d{4}$/',
	'month' => '/^\d{0,1,2}$/',
	'day' => '/^\d{0,1,2}$/'
);

// you simply append a hash at the end of your mapping to set any default parameters.
$map['/posts/:action/:id'] = array (
	'controller' => 'posts',
	'id' => '/\d{1,}/',
	'defaults' => array (
		'page' => 1,
		'numbers' => 30
	)
);

// conditions route for http verb request.
$map['/posts/:id'] = array (
	array (
		'controller' => 'posts',
		'action' => 'show',
		'id' => '/\d{1,}/',
		'defaults' => array (
			'page' => 1,
			'numbers' => 30
		),
		'conditions' => array (
			'method' => 'get'
		)
	),
	array (
		'controller' => 'posts',
		'action' => 'create_comment',
		'conditions' => array (
			'method' => 'post'
		)
	),
	array (
		'controller' => 'posts',
		'action' => 'update',
		'conditions' => array (
			'method' => 'put'
		)
	),
	array (
		'controller' => 'posts',
		'action' => 'destroy',
		'conditions' => array (
			'method' => 'delete'
		)
	)
);

// temporary redirect routes.
$map['/test/test/test'] = array (
	'location' => '/500.html'
);
$map['/test/test/test/test1'] = array (
	'location' => '/404.html'
);
$map['/login2'] = array (
	'location' => '/admin/login'
);

$map['/blog/:id'] = array (
	'controller' => 'admin',
	'action' => 'test',
	'id' => '/\d{1,}/'
);

// default route.
$map['/:controller/:action/:id'] = array ();

// globbing route, gracefully handle badly formed requests.
$map['/:controller/:action/:id/*others'] = array ();

$map['*path'] = array (
	'controller' => 'admin',
	'action' => 'test',
	'status' => 404
);
?>
