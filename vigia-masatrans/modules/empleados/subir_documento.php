<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$empleado_id = $_GET['id'];

$queryEmpleado = mysqli_query($conn,"
SELECT * FROM empleados
WHERE id='$empleado_id'
");

$empleado = mysqli_fetch_assoc($queryEmpleado);

if(isset($_POST['guardar'])){

    $nombre_documento = $_POST['nombre_documento'];

    $archivo = $_FILES['archivo']['name'];

    $tmp = $_FILES['archivo']['tmp_name'];

    // CREAR NOMBRE ÚNICO

    $nombreFinal = time().'_'.$archivo;

    // RUTA

    $ruta = "../../uploads/documentos/".$nombreFinal;

    // SUBIR ARCHIVO

    move_uploaded_file($tmp,$ruta);

    // INSERTAR EN DB

    $query = "

    INSERT INTO documentos(

        empleado_id,
        nombre_documento,
        archivo

    ) VALUES (

        '$empleado_id',
        '$nombre_documento',
        '$nombreFinal'

    )

    ";

    mysqli_query($conn,$query);

    header("Location: perfil.php?id=".$empleado_id);

}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Subir Documento | VIGIA MASATRANS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="../../assets/css/style.css">

<style>

.form-card{
    background:white;
    border-radius:20px;
    padding:30px;
}

</style>

</head>
<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">
        VIGIA MASATRANS
    </div>

    <a href="../../dashboard.php">
        📊 Dashboard
    </a>

    <a href="empleados.php">
        👷 Empleados
    </a>

    <a href="../../logout.php">
        🚪 Cerrar sesión
    </a>

</div>

<!-- MAIN -->

<div class="main-content">

    <div class="mb-4">

        <h2 class="fw-bold">
            📄 Subir Documento
        </h2>

        <small class="text-muted">

            <?= $empleado['nombres'] ?>
            <?= $empleado['apellidos'] ?>

        </small>

    </div>

    <div class="form-card shadow">

        <form method="POST"
        enctype="multipart/form-data">

            <div class="mb-3">

                <label class="form-label">
                    Tipo documento
                </label>

                <select
                name="nombre_documento"
                class="form-control"
                required>

                    <option value="">
                        Seleccionar
                    </option>

                    <option>
                        RUT
                    </option>

                    <option>
                        Hoja de Vida
                    </option>

                    <option>
                        Certificado Residencia
                    </option>

                    <option>
                        Uso de Imagen
                    </option>

                    <option>
                        Alcohol y Drogas
                    </option>

                    <option>
                        Certificado Alturas
                    </option>

                    <option>
                        Certificado Operador Grúa
                    </option>

                    <option>
                        Certificado Aparejador
                    </option>

                    <option>
                        Certificado Médico
                    </option>

                    <option>
                        Licencia Conducción
                    </option>

                </select>

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Archivo PDF
                </label>

                <input
                type="file"
                name="archivo"
                class="form-control"
                accept=".pdf,.jpg,.png,.jpeg"
                required>

            </div>

            <button
            type="submit"
            name="guardar"
            class="btn btn-primary">

                Guardar Documento

            </button>

            <a href="perfil.php?id=<?= $empleado_id ?>"
            class="btn btn-secondary">

                Volver

            </a>

        </form>

    </div>

</div>

</body>
</html>