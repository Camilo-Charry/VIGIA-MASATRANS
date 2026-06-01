<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$doc_id = $_GET['doc_id'];
$empleado_id = $_GET['empleado_id'];

$result = mysqli_query($conn,"SELECT nombre_archivo FROM documentos_empleado WHERE id='$doc_id'");
$doc = mysqli_fetch_assoc($result);

if($doc['nombre_archivo']){
    $archivo = "../../uploads/documentos/" . $doc['nombre_archivo'];
    if(file_exists($archivo)){
        unlink($archivo);
    }
}

mysqli_query($conn,"DELETE FROM documentos_empleado WHERE id='$doc_id'");

header("Location: perfil.php?id=$empleado_id");
exit();