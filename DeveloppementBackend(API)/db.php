<?php
session_start();
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "greenmarket";
$conn = mysqli_connect($host, $user, $pass, $dbname);
if(!$conn) die("Erreur de connexion : ".mysqli_connect_error());
