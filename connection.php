<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);


$dbhost = "localhost";
$dbuser = "webuser"; //webuser
$dbpass = getenv('DATABASE_PASSWORD'); //getenv('DATABASE_PASSWORD') for production, hardcoded for testing
$dbname = "client_server_db";

if(!$con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname)){

    die("failed to connect!");
}
