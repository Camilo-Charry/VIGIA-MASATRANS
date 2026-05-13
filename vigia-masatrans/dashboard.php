<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

include("config/database.php");

$empleados = mysqli_num_rows(mysqli_query($conn,"
SELECT id FROM empleados
"));

$cursos_vencidos = mysqli_num_rows(mysqli_query($conn,"

SELECT id FROM empleado_cursos
WHERE fecha_vencimiento < CURDATE()

"));

$cursos_por_vencer = mysqli_num_rows(mysqli_query($conn,"

SELECT id FROM empleado_cursos

WHERE fecha_vencimiento
BETWEEN CURDATE()
AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)

"));

$vacunas = mysqli_num_rows(mysqli_query($conn,"
SELECT id FROM vacunas
"));

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Dashboard | VIGIA MASATRANS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="assets/css/style.css">

<style>

.card-dashboard{
    border:none;
    border-radius:25px;
    color:white;
    overflow:hidden;
}

.bg1{
    background:linear-gradient(135deg,#2563eb,#1e40af);
}

.bg2{
    background:linear-gradient(135deg,#dc2626,#991b1b);
}

.bg3{
    background:linear-gradient(135deg,#f59e0b,#d97706);
}

.bg4{
    background:linear-gradient(135deg,#16a34a,#166534);
}

.icon-box{
    font-size:45px;
    opacity:0.3;
}

.alert-card{
    border:none;
    border-radius:20px;
}

</style>

</head>
<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">
        VIGIA MASATRANS
    </div>

    <a href="dashboard.php">
        📊 Dashboard
    </a>

    <a href="modules/empleados/empleados.php">
        👷 Empleados
    </a>

    <a href="logout.php">
        🚪 Cerrar sesión
    </a>

</div>

<!-- MAIN -->

<div class="main-content">

    <div class="mb-5">

        <h1 class="fw-bold">
            Dashboard HSEQ
        </h1>

        <p class="text-muted">
            Panel ejecutivo • VIGIA MASATRANS
        </p>

    </div>

    <!-- CARDS -->

    <div class="row g-4 mb-5">

        <div class="col-md-3">

            <div class="card card-dashboard bg1 shadow">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>
                                Empleados
                            </h6>

                            <h1 class="fw-bold">

                                <?= $empleados ?>

                            </h1>

                        </div>

                        <div class="icon-box">
                            👷
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card card-dashboard bg2 shadow">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>
                                Cursos Vencidos
                            </h6>

                            <h1 class="fw-bold">

                                <?= $cursos_vencidos ?>

                            </h1>

                        </div>

                        <div class="icon-box">
                            🚨
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card card-dashboard bg3 shadow">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>
                                Por Vencer
                            </h6>

                            <h1 class="fw-bold">

                                <?= $cursos_por_vencer ?>

                            </h1>

                        </div>

                        <div class="icon-box">
                            ⚠️
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card card-dashboard bg4 shadow">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6>
                                Vacunas
                            </h6>

                            <h1 class="fw-bold">

                                <?= $vacunas ?>

                            </h1>

                        </div>

                        <div class="icon-box">
                            💉
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- ALERTAS -->

    <div class="card alert-card shadow">

        <div class="card-body">

            <h4 class="mb-4">
                🚨 Alertas Automáticas
            </h4>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>Empleado</th>
                            <th>Curso</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $queryAlertas = "

                    SELECT

                    empleados.nombres,
                    empleados.apellidos,

                    cursos.nombre AS curso,

                    empleado_cursos.fecha_vencimiento

                    FROM empleado_cursos

                    INNER JOIN empleados
                    ON empleado_cursos.empleado_id = empleados.id

                    INNER JOIN cursos
                    ON empleado_cursos.curso_id = cursos.id

                    ORDER BY empleado_cursos.fecha_vencimiento ASC

                    LIMIT 20

                    ";

                    $resultadoAlertas = mysqli_query($conn,$queryAlertas);

                    while($alerta = mysqli_fetch_assoc($resultadoAlertas)){

                        $hoy = date('Y-m-d');

                        $vencimiento = $alerta['fecha_vencimiento'];

                        $dias = (strtotime($vencimiento)-strtotime($hoy))/86400;

                        if($dias < 0){

                            $estado = "VENCIDO";
                            $badge = "danger";

                        }elseif($dias <= 30){

                            $estado = "POR VENCER";
                            $badge = "warning";

                        }else{

                            $estado = "VIGENTE";
                            $badge = "success";

                        }

                    ?>

                    <tr>

                        <td>

                            <?= $alerta['nombres'] ?>
                            <?= $alerta['apellidos'] ?>

                        </td>

                        <td>

                            <?= $alerta['curso'] ?>

                        </td>

                        <td>

                            <?= $alerta['fecha_vencimiento'] ?>

                        </td>

                        <td>

                            <span class="badge bg-<?= $badge ?>">

                                <?= $estado ?>

                            </span>

                        </td>

                    </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>