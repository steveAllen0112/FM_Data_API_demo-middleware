<?php
use airmoi\FileMaker\Record;
#use airmoi\FileMaker\Exception;
use airmoi\FileMaker\FileMakerException;

use Slim\Http\Request;
use Slim\Http\Response;

$dir = dirname(__FILE__);
$includes = "$dir/../public/includes/";
$routes = "$includes/Routes/";

require_once("$includes/master.php");

// Routes
include("$routes/authenticate-post.php");
include("$routes/blackoutDates-get.php");
include("$routes/timeSlots-get.php");