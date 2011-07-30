<?php 
//As we add more schemas --- alter USER beagle_dev set search_path to system,public;
//$dbconn = pg_connect("host=localhost port=5432 dbname=beaglecrm_dev user=beagle_dev password=beagle_dev");
$dsn = array('host'=>'localhost',
				'port'=>'5432',
				'dbname'=>'beagle_dev',
				'user'=>'beagle_dev',
				'password'=>'m0cha');

$GLOBALS['DB'] = new pgdb($dsn);// $dbconn;



?>