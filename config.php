<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "authentication_dbms";

$conn = mysqli_connect($host, $user, $password, $database);

if ($conn -> connect_error) {
    die("Connection failed: " . mysqli_connect_error());
}

?>