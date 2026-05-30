<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$empleado_id = $_POST['empleado_id'];

if(isset($_FILES['foto']) && $_FILES['foto']['size'] > 0){

    $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $extensiones_validas = ['jpg','jpeg','png','webp'];

    if(!in_array(strtolower($extension), $extensiones_validas)){
        header("Location: perfil.php?id=$empleado_id&error=formato");
        exit();
    }

    // Eliminar foto anterior
    $resultFoto = mysqli_query($conn,"SELECT foto FROM empleados WHERE id='$empleado_id'");
    $dataFoto = mysqli_fetch_assoc($resultFoto);
    if($dataFoto['foto']){
        $fotoVieja = "../../uploads/fotos/" . $dataFoto['foto'];
        if(file_exists($fotoVieja)){
            unlink($fotoVieja);
        }
    }

    // Subir nueva foto
    if(!is_dir("../../uploads/fotos/")){
        mkdir("../../uploads/fotos/", 0777, true);
    }

    $nombre_foto = time() . '_' . $empleado_id . '.' . $extension;
    $destino = "../../uploads/fotos/" . $nombre_foto;
    move_uploaded_file($_FILES['foto']['tmp_name'], $destino);

    mysqli_query($conn,"UPDATE empleados SET foto='$nombre_foto' WHERE id='$empleado_id'");

}

header("Location: perfil.php?id=$empleado_id");
exit();