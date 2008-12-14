<?php
/**
 * routes map config.
 * array_keys list below:
 *
 * controller: 	controller name
 * action:		action name
 * id:			id
 * defaults:	default params append to request params array
 * get/post/put/delete: http restful request methods.
 * resources:	resources name used as key name.
 * variables:	variables in key can be used in array as key name.
 */

$map = array();

// named routes
$map['root'] = array('controller' => 'page');
$map['login'] = array('controller' => 'page', 'action' => 'login');
$map['logout'] = array('controller' => 'page', 'action' => 'logout');

// defining RESTful resources and nested resources
$map['resources'] = array('clients', 'posts', 'users' => array('clients', 'posts'));

// regular expressions and parameters
$map['/blog/$id'] = array('controller' => 'post', 'action' => 'show', 'id' => '/\d{1,}/');
$map['/articles/$year/$month/$day'] = array('controller' => 'articles', 'action' => 'find_by_date', 'year' => '/\d{4}/', 'month' => '/\d{1,2}/', 'day' => '/\d{1,2}/');

// you simply append a hash at the end of your mapping to set any default parameters.
$map['/post/$action/$id'] = array('controller' => 'post', 'id' => '/\d{1,}/', 'defaults' => array('page' => 1, 'numbers' => 30));

// route conditions for http restful request
$map['/post/$id'] = array('get' => array('controller' => 'posts', 'action' => 'show'), 'post' => array('controller' => 'posts', 'action' => 'create_comment'), 'put' => array('controller' => 'posts', 'action' => 'update'), 'delete' => array('controller' => 'posts', 'action' => 'destroy'));

// default route
$map['/$controller/$action/$id'] = array();

// globbing route for any path, specifying *[string] as part of a rule like
$map['*path'] = array('controller' => 'page', 'action' => 'path');
$map['*'] = array('controller' => 'page', 'action' => 'anything');

Router :: map($map);
?>