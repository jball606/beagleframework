<?php
/**
 * This is the main file to the beagle framework.  You must have beaglereqfunctions to even think about starting to use this frameowrk.
 * This follows the MVCC framework.  The extra C is for classes that you put the business logic into.  With this fraemework we bring PHP into a full OO langauge
 * PHP 5 or above required
 * 
 * Copyright 2011, Jason Ball
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * This code is free to use as long as this disclaimer is at the top of the systemsetup.php page	 
 * 
 * Date 2011-07-23
 *
 *File structure
 *
 *webroot (htdocs)
 *lib
 *	beaglelib (core)
 *	classes (user classes)
 *		model (db)
 *	views
 *
 */
 

define("__WEB_ROOT__",'htdocs');
define("__USERKEY__","user_id");
error_reporting(-1);
session_start();
include_once("beaglelib/beaglereqfunctions.php");
include_once("beaglelib/breadcrumbclass.php");
require_once("db_inc.inc");
setGlobalVars();

/* System specific code is below */
