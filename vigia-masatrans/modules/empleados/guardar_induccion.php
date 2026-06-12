<?php
session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $empleado_id     = intval($_POST['empleado_id']);
    $fecha_realizacion = $_POST['fecha_realizacion'];
    $observaciones   = $_POST['observaciones'] ?? '';

    // Calcular vencimiento: +12 meses
    $fecha_vencimiento = date('Y-m-d', strtotime($fecha_realizacion . ' +12 months'));

    // Subir PDF si viene
    $documento_pdf = null;
    if(isset($_FILES['documento_pdf']) && $_FILES['documento_pdf']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['documento_pdf']['name'], PATHINFO_EXTENSION));
        if($ext === 'pdf'){
            $carpeta = '../../uploads/inducciones/';
            if(!is_dir($carpeta)){
                mkdir($carpeta, 0755, true);
            }
            $nombre_archivo = 'ind_' . $empleado_id . '_' . time() . '.pdf';
            move_uploaded_file($_FILES['documento_pdf']['tmp_name'], $carpeta . $nombre_archivo);
            $documento_pdf = $nombre_archivo;
        }
    }

    // Verificar si ya existe una inducción para este empleado
    $checkQuery = "SELECT id FROM inducciones WHERE empleado_id = $empleado_id";
    $checkResult = mysqli_query($conn, $checkQuery);

    if(mysqli_num_rows($checkResult) > 0){
        // Actualizar
        $row = mysqli_fetch_assoc($checkResult);
        $id_ind = $row['id'];

        if($documento_pdf){
            $sql = "UPDATE inducciones SET fecha_realizacion='$fecha_realizacion', fecha_vencimiento='$fecha_vencimiento', documento_pdf='$documento_pdf', observaciones='$observaciones' WHERE id=$id_ind";
        } else {
            $sql = "UPDATE inducciones SET fecha_realizacion='$fecha_realizacion', fecha_vencimiento='$fecha_vencimiento', observaciones='$observaciones' WHERE id=$id_ind";
        }
    } else {
        // Insertar
        $sql = "INSERT INTO inducciones (empleado_id, fecha_realizacion, fecha_vencimiento, documento_pdf, observaciones) VALUES ('$empleado_id', '$fecha_realizacion', '$fecha_vencimiento', " . ($documento_pdf ? "'$documento_pdf'" : "NULL") . ", '$observaciones')";
    }

    mysqli_query($conn, $sql);

    header("Location: empleados.php?induccion=ok");
    exit();
}

header("Location: empleados.php");
exit();
?>