<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$id = $_GET['id'];

if(isset($_POST['guardar'])){

    $curso_id = $_POST['curso_id'];

    $fecha_realizacion = $_POST['fecha_realizacion'];

    $fecha_vencimiento = date(
        'Y-m-d',
        strtotime($fecha_realizacion . ' +1 year')
    );

    $query = "INSERT INTO empleado_cursos(

        empleado_id,
        curso_id,
        fecha_realizacion,
        fecha_vencimiento

    ) VALUES (

        '$id',
        '$curso_id',
        '$fecha_realizacion',
        '$fecha_vencimiento'

    )";

    mysqli_query($conn,$query);

    header("Location: perfil.php?id=$id");

}

$cursos = mysqli_query($conn,"SELECT * FROM cursos");

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Agregar Curso</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
}

.card{
    border:none;
    border-radius:20px;
}

</style>

</head>
<body>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-body p-5">

            <div class="mb-4">

                <h2>
                    📚 Agregar Curso
                </h2>

                <small class="text-muted">
                    Gestión de cursos y vigencias
                </small>

            </div>

            <form method="POST">

                <div class="mb-4">

                    <label class="mb-2">
                        Curso
                    </label>

                    <select name="curso_id"
                    class="form-control"
                    required>

                        <option value="">
                            Seleccionar curso
                        </option>

                        <?php while($curso = mysqli_fetch_assoc($cursos)){ ?>

                            <option value="<?= $curso['id'] ?>">

                                <?= $curso['nombre'] ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>

                <div class="mb-4">

                    <label class="mb-2">
                        Fecha de realización
                    </label>

                    <input type="date"
                    name="fecha_realizacion"
                    class="form-control"
                    required>

                </div>

                <button type="submit"
                name="guardar"
                class="btn btn-primary btn-lg">

                    Guardar Curso

                </button>

                <a href="perfil.php?id=<?= $id ?>"
                class="btn btn-secondary btn-lg">

                    Volver

                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>