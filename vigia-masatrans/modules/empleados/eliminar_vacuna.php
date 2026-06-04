<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$empleado_id = $_GET['empleado_id'];

// Eliminar PDF si existe
$result = mysqli_query($conn,"SELECT pdf_vacuna FROM vacunas_empleado WHERE empleado_id='$empleado_id'");
$vacuna = mysqli_fetch_assoc($result);

if($vacuna['pdf_vacuna']){
    $archivo = "../../uploads/vacunas/" . $vacuna['pdf_vacuna'];
    if(file_exists($archivo)){
        unlink($archivo);
    }
}

mysqli_query($conn,"DELETE FROM vacunas_empleado WHERE empleado_id='$empleado_id'");

header("Location: perfil.php?id=$empleado_id");
exit();