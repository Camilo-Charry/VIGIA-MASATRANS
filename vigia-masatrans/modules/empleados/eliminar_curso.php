<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$curso_id = $_GET['curso_id'];
$empleado_id = $_GET['empleado_id'];

// Obtener pdf para eliminarlo del servidor
$result = mysqli_query($conn,"SELECT pdf_soporte FROM empleado_cursos WHERE id='$curso_id'");
$registro = mysqli_fetch_assoc($result);

if($registro['pdf_soporte']){
    $archivo = "../../uploads/cursos/" . $registro['pdf_soporte'];
    if(file_exists($archivo)){
        unlink($archivo);
    }
}

mysqli_query($conn,"DELETE FROM empleado_cursos WHERE id='$curso_id'");

header("Location: perfil.php?id=$empleado_id");
exit();