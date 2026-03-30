<?php 

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "client_server_db";

if(!$con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname)){

    die("failed to connect!");
}
