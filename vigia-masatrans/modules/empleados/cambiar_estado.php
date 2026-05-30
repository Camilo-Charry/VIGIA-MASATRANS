<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$empleado_id = $_GET['id'];
$nuevo_estado = $_GET['estado'];

// Validar que el estado sea válido
$estados_validos = ['ACTIVO', 'INACTIVO', 'RETIRADO'];
if(!in_array($nuevo_estado, $estados_validos)){
    header("Location: empleados.php");
    exit();
}

mysqli_query($conn,"
UPDATE empleados SET estado='$nuevo_estado'
WHERE id='$empleado_id'
");

header("Location: perfil.php?id=$empleado_id");
exit();