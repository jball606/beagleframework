<?php 
$killsession = 1;
$getcwd = getcwd();
$lib = str_replace('/bin', '', $getcwd);
ini_set ('include_path', ".:$getcwd:$lib/lib");
?>