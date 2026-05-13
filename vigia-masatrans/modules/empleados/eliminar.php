<?php

session_start();

include("../../config/database.php");

$id = $_GET['id'];

mysqli_query($conn,"
DELETE FROM empleados
WHERE id='$id'
");

header("Location: empleados.php");

?>