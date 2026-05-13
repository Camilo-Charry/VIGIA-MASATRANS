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

    $tipo_vacuna = $_POST['tipo_vacuna'];
    $dosis = $_POST['dosis'];
    $fecha_aplicacion = $_POST['fecha_aplicacion'];
    $observaciones = $_POST['observaciones'];

    $query = "

    INSERT INTO vacunas(

        empleado_id,
        tipo_vacuna,
        dosis,
        fecha_aplicacion,
        observaciones

    ) VALUES (

        '$empleado_id',
        '$tipo_vacuna',
        '$dosis',
        '$fecha_aplicacion',
        '$observaciones'

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

<title>Agregar Vacuna</title>

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

</div>

<div class="main-content">

    <div class="mb-4">

        <h2 class="fw-bold">
            💉 Agregar Vacuna
        </h2>

        <small class="text-muted">

            <?= $empleado['nombres'] ?>
            <?= $empleado['apellidos'] ?>

        </small>

    </div>

    <div class="form-card shadow">

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>
                        Tipo vacuna
                    </label>

                    <select
                    name="tipo_vacuna"
                    class="form-control">

                        <option>
                            Fiebre Amarilla
                        </option>

                        <option>
                            COVID-19
                        </option>

                        <option>
                            Tétano
                        </option>

                        <option>
                            Hepatitis B
                        </option>

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label>
                        Dosis
                    </label>

                    <select
                    name="dosis"
                    class="form-control">

                        <option>
                            Dosis Única
                        </option>

                        <option>
                            1ra Dosis
                        </option>

                        <option>
                            2da Dosis
                        </option>

                        <option>
                            3ra Dosis
                        </option>

                        <option>
                            4ta Dosis
                        </option>

                        <option>
                            5ta Dosis
                        </option>

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label>
                        Fecha aplicación
                    </label>

                    <input type="date"
                    name="fecha_aplicacion"
                    class="form-control">

                </div>

                <div class="col-md-12 mb-3">

                    <label>
                        Observaciones
                    </label>

                    <textarea
                    name="observaciones"
                    class="form-control"
                    rows="4"></textarea>

                </div>

            </div>

            <button
            type="submit"
            name="guardar"
            class="btn btn-primary">

                Guardar Vacuna

            </button>

        </form>

    </div>

</div>

</body>
</html>