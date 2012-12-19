<?php
define('__ROOT__',dirname(dirname( dirname(__FILE__))));
require_once(__ROOT__.'/inc/listController.php'); 
ListController::LoadAll();
?>