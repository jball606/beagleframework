<?php 

$dsn = array('host'=>'localhost',
				'port'=>'5432',
				'dbname'=>'beagle_db',
				'user'=>'beagle_user',
				'password'=>'password');

$GLOBALS['DB'] = new pgdb($dsn);// $dbconn;



?>