<?php
defined('SYSPATH') OR exit('No direct script access allowed');

/**
 * Initialize the database
 *
 * @category	Database
 * @author	EllisLab Dev Team
 * @link	https://codeigniter.com/user_guide/database/
 *
 * @param 	string|string[]	$params
 * @param 	bool		$query_builder_override
 *				Determines if query builder should be used or not
 */
function DB($config = 'default')
{
	// Load the DB config file if a DSN string wasn't passed
	if ( ! file_exists($file_path = 'src/config/database.php'))
	{
		err('The configuration file database.php does not exist.');
	}

	include($file_path);
	$params = $db[$config];

	// No DB specified yet? Beat them senseless...
	if (empty($params['dbdriver']))
	{
		err('You have not selected a database type to connect to.');
	}

	require_once(SYSPATH.'database/DB_driver.php');
	require_once(SYSPATH.'database/DB_query_builder.php');
	
	if (!class_exists('CI_DB')){
		class CI_DB extends query_builder { }
	}
	
	// Load the DB driver
	$driver_file = SYSPATH.'database/drivers/'.$params['dbdriver'].'/'.$params['dbdriver'].'_driver.php';

	file_exists($driver_file) OR err('Invalid DB driver');
	require_once($driver_file);

	// Instantiate the DB adapter
	$driver = 'CI_DB_'.$params['dbdriver'].'_driver';
	$DB = new $driver($params);

	$DB->initialize();
	return $DB;
}
