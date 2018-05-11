<?php
use Slim\Http\Request;
use Slim\Http\Response;

$dir = dirname(__FILE__);
$includes = "$dir/../public/includes/";
$routes = "$includes/Routes/";

require_once("$includes/master.php");

// Routes
include("$routes/contact-post.php");