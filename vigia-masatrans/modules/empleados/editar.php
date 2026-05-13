<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$id = $_GET['id'];

$query = mysqli_query($conn,"
SELECT * FROM empleados
WHERE id='$id'
");

$empleado = mysqli_fetch_assoc($query);

if(isset($_POST['actualizar'])){

    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $cedula = $_POST['cedula'];
    $cargo = $_POST['cargo'];
    $celular = $_POST['celular'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $ciudad = $_POST['ciudad'];
    $departamento = $_POST['departamento'];
    $eps = $_POST['eps'];
    $arl = $_POST['arl'];
    $pension = $_POST['pension'];
    $salario_base = $_POST['salario_base'];

    mysqli_query($conn,"

    UPDATE empleados SET

    nombres='$nombres',
    apellidos='$apellidos',
    cedula='$cedula',
    cargo='$cargo',
    celular='$celular',
    correo='$correo',
    direccion='$direccion',
    ciudad='$ciudad',
    departamento='$departamento',
    eps='$eps',
    arl='$arl',
    pension='$pension',
    salario_base='$salario_base'

    WHERE id='$id'

    ");

    header("Location: perfil.php?id=".$id);

}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Editar Empleado</title>

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
            ✏️ Editar Empleado
        </h2>

    </div>

    <div class="form-card shadow">

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>
                        Nombres
                    </label>

                    <input
                    type="text"
                    name="nombres"
                    class="form-control"
                    value="<?= $empleado['nombres'] ?>">

                </div>

                <div class="col-md-6 mb-3">

                    <label>
                        Apellidos
                    </label>

                    <input
                    type="text"
                    name="apellidos"
                    class="form-control"
                    value="<?= $empleado['apellidos'] ?>">

                </div>

                <div class="col-md-6 mb-3">

                    <label>
                        Cédula
                    </label>

                    <input
                    type="text"
                    name="cedula"
                    class="form-control"
                    value="<?= $empleado['cedula'] ?>">

                </div>

                <div class="col-md-6 mb-3">

                    <label>
                        Cargo
                    </label>

                    <input
                    type="text"
                    name="cargo"
                    class="form-control"
                    value="<?= $empleado['cargo'] ?>">

                </div>

                <div class="col-md-6 mb-3">

                    <label>
                        Celular
                    </label>

                    <input
                    type="text"
                    name="celular"
                    class="form-control"
                    value="<?= $empleado['celular'] ?>">

                </div>

                <div class="col-md-6 mb-3">

                    <label>
                        Correo
                    </label>

                    <input
                    type="email"
                    name="correo"
                    class="form-control"
                    value="<?= $empleado['correo'] ?>">

                </div>

                <div class="col-md-12 mb-3">

                    <label>
                        Dirección
                    </label>

                    <input
                    type="text"
                    name="direccion"
                    class="form-control"
                    value="<?= $empleado['direccion'] ?>">

                </div>

                <div class="col-md-4 mb-3">

                    <label>
                        Ciudad
                    </label>

                    <input
                    type="text"
                    name="ciudad"
                    class="form-control"
                    value="<?= $empleado['ciudad'] ?>">

                </div>

                <div class="col-md-4 mb-3">

                    <label>
                        Departamento
                    </label>

                    <input
                    type="text"
                    name="departamento"
                    class="form-control"
                    value="<?= $empleado['departamento'] ?>">

                </div>

                <div class="col-md-4 mb-3">

                    <label>
                        EPS
                    </label>

                    <input
                    type="text"
                    name="eps"
                    class="form-control"
                    value="<?= $empleado['eps'] ?>">

                </div>

                <div class="col-md-4 mb-3">

                    <label>
                        ARL
                    </label>

                    <input
                    type="text"
                    name="arl"
                    class="form-control"
                    value="<?= $empleado['arl'] ?>">

                </div>

                <div class="col-md-4 mb-3">

                    <label>
                        Pensión
                    </label>

                    <input
                    type="text"
                    name="pension"
                    class="form-control"
                    value="<?= $empleado['pension'] ?>">

                </div>

                <div class="col-md-4 mb-3">

                    <label>
                        Salario Base
                    </label>

                    <input
                    type="number"
                    name="salario_base"
                    class="form-control"
                    value="<?= $empleado['salario_base'] ?>">

                </div>

            </div>

            <button
            type="submit"
            name="actualizar"
            class="btn btn-primary">

                Actualizar Empleado

            </button>

            <a href="perfil.php?id=<?= $id ?>"
            class="btn btn-secondary">

                Volver

            </a>

        </form>

    </div>

</div>

</body>
</html>