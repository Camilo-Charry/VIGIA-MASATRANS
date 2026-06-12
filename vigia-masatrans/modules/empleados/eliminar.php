<?php

session_start();

include("../../config/database.php");

$id = intval($_GET['id']);

// Deshabilitar temporalmente la verificación de claves foráneas
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

// Eliminar todas las tablas hijas (orden exacto según tu BD)
mysqli_query($conn, "DELETE FROM antecedentes_empleado WHERE empleado_id = $id");
mysqli_query($conn, "DELETE FROM documentos_empleado WHERE empleado_id = $id");
mysqli_query($conn, "DELETE FROM documentos_obligatorios WHERE empleado_id = $id");
mysqli_query($conn, "DELETE FROM empleado_cursos WHERE empleado_id = $id");
mysqli_query($conn, "DELETE FROM inducciones WHERE empleado_id = $id");
mysqli_query($conn, "DELETE FROM licencias WHERE empleado_id = $id");
mysqli_query($conn, "DELETE FROM vacunas_empleado WHERE empleado_id = $id");

// Eliminar el empleado
mysqli_query($conn, "DELETE FROM empleados WHERE id = $id");

// Volver a habilitar la verificación de claves foráneas
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

header("Location: empleados.php?msg=eliminado");
exit();

?>