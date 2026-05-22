<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$id = $_GET['id'];

$query = "SELECT * FROM empleados WHERE id='$id'";
$resultado = mysqli_query($conn,$query);

$empleado = mysqli_fetch_assoc($resultado);

$queryLicencia = "
SELECT * FROM licencias
WHERE empleado_id='$id'
LIMIT 1
";

$resultLicencia = mysqli_query($conn,$queryLicencia);

$licencia = mysqli_fetch_assoc($resultLicencia);

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Perfil Empleado | VIGIA MASATRANS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../../assets/css/style.css">

<style>

.profile-header{
    background:linear-gradient(135deg,#0f172a,#1e293b);
    border-radius:25px;
    padding:35px;
    color:white;
}

.profile-photo{
    width:120px;
    height:120px;
    border-radius:50%;
    background:#2563eb;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    font-weight:bold;
    margin:auto;
}

.info-card{
    border:none;
    border-radius:20px;
}

.info-item{
    background:#f8fafc;
    border-radius:15px;
    padding:20px;
    height:100%;
}

.info-label{
    color:#64748b;
    font-size:14px;
}

.info-value{
    font-weight:600;
    font-size:16px;
    color:#0f172a;
}

.nav-tabs .nav-link{
    border:none;
    color:#334155;
    font-weight:600;
}

.nav-tabs .nav-link.active{
    background:#2563eb;
    color:white;
    border-radius:10px;
}

</style>

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sidebar-logo">
    <div class="brand">
      <div class="brand-logo"></div>
      <span>
        VIGIA MASATRANS
        <div class="brand-sub">Panel Corporativo</div>
      </span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <a href="../../dashboard.php">
      <span class="nav-icon">📊</span>
      Dashboard
    </a>
    <a href="empleados.php" class="active">
      <span class="nav-icon">👷</span>
      Empleados
    </a>
    <a href="../../logout.php">
      <span class="nav-icon">🚪</span>
      Cerrar sesión
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="../../dashboard.php">
      <span class="nav-icon">ℹ️</span>
      Control HSEQ
    </a>
  </div>
</div>

<!-- MAIN -->

<div class="main-content">

    <!-- HEADER -->

    <div class="profile-header shadow">

        <div class="row align-items-center">

            <div class="col-md-2 text-center">

                <div class="profile-photo">

                    <?= strtoupper(substr($empleado['nombres'],0,1)) ?>

                </div>

            </div>

            <div class="col-md-10">

                <h2 class="fw-bold">

                    <?= $empleado['nombres'] ?>
                    <?= $empleado['apellidos'] ?>

                </h2>

                <p class="mb-1">

                    <?= $empleado['cargo'] ?>

                </p>

                <small>

                    Cédula:
                    <?= $empleado['cedula'] ?>

                </small>

                <div class="mt-3">

                    <?php if($empleado['rol_conductor'] == 'SI'){ ?>

                        <span class="badge bg-warning text-dark">
                            Conductor
                        </span>

                    <?php } ?>

                    <span class="badge bg-success">
                        ACTIVO
                    </span>

                </div>

            </div>

        </div>

    </div>

    <!-- TABS -->

    <ul class="nav nav-tabs mt-4 mb-4">

        <li class="nav-item">

            <button class="nav-link active"
            data-bs-toggle="tab"
            data-bs-target="#general">

                👤 General

            </button>

        </li>

        <li class="nav-item">

            <button class="nav-link"
            data-bs-toggle="tab"
            data-bs-target="#laboral">

                🚛 Laboral

            </button>

        </li>

        <li class="nav-item">

            <button class="nav-link"
            data-bs-toggle="tab"
            data-bs-target="#licencia">

                🚘 Licencia

            </button>

        </li>

        <li class="nav-item">

            <button class="nav-link"
            data-bs-toggle="tab"
            data-bs-target="#seguridad">

                🏥 Seguridad Social

            </button>

        </li>

        <li class="nav-item">

            <button class="nav-link"
            data-bs-toggle="tab"
            data-bs-target="#cursos">

                📚 Cursos

            </button>

        </li>

        <li class="nav-item">

            <button class="nav-link"
            data-bs-toggle="tab"
            data-bs-target="#vacunas">

                💉 Vacunas

            </button>

        </li>

    </ul>

    <div class="tab-content">

        <!-- GENERAL -->

        <div class="tab-pane fade show active"
        id="general">

            <div class="card info-card shadow">

                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    RH
                                </div>

                                <div class="info-value">
                                    <?= $empleado['rh'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Fecha nacimiento
                                </div>

                                <div class="info-value">
                                    <?= $empleado['fecha_nacimiento'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Género
                                </div>

                                <div class="info-value">
                                    <?= $empleado['genero'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="info-item">

                                <div class="info-label">
                                    Dirección
                                </div>

                                <div class="info-value">
                                    <?= $empleado['direccion'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="info-item">

                                <div class="info-label">
                                    Ciudad
                                </div>

                                <div class="info-value">
                                    <?= $empleado['ciudad_residencia'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="info-item">

                                <div class="info-label">
                                    Departamento
                                </div>

                                <div class="info-value">
                                    <?= $empleado['departamento_residencia'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Celular
                                </div>

                                <div class="info-value">
                                    <?= $empleado['celular'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Correo
                                </div>

                                <div class="info-value">
                                    <?= $empleado['correo'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Nivel Académico
                                </div>

                                <div class="info-value">
                                    <?= $empleado['nivel_academico'] ?>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- LABORAL -->

        <div class="tab-pane fade"
        id="laboral">

            <div class="card info-card shadow">

                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Cargo
                                </div>

                                <div class="info-value">
                                    <?= $empleado['cargo'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Fecha ingreso
                                </div>

                                <div class="info-value">
                                    <?= $empleado['fecha_ingreso'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Turno
                                </div>

                                <div class="info-value">
                                    <?= $empleado['turno_trabajo'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Contrato
                                </div>

                                <div class="info-value">
                                    <?= $empleado['tipo_contrato'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Base operación
                                </div>

                                <div class="info-value">
                                    <?= $empleado['base_operacion'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    SIPLAFT
                                </div>

                                <div class="info-value">
                                    <?= $empleado['siplaft'] ?>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- LICENCIA -->

        <div class="tab-pane fade"
        id="licencia">

            <div class="card info-card shadow">

                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Categoría
                                </div>

                                <div class="info-value">
                                    <?= $licencia['categoria'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Vencimiento
                                </div>

                                <div class="info-value">
                                    <?= $licencia['fecha_vencimiento'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Restricciones
                                </div>

                                <div class="info-value">
                                    <?= $licencia['restricciones'] ?>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- SEGURIDAD SOCIAL -->

        <div class="tab-pane fade"
        id="seguridad">

            <div class="card info-card shadow">

                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-3">

                            <div class="info-item">

                                <div class="info-label">
                                    EPS
                                </div>

                                <div class="info-value">
                                    <?= $empleado['eps'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="info-item">

                                <div class="info-label">
                                    ARL
                                </div>

                                <div class="info-value">
                                    <?= $empleado['arl'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="info-item">

                                <div class="info-label">
                                    Pensión
                                </div>

                                <div class="info-value">
                                    <?= $empleado['pension'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="info-item">

                                <div class="info-label">
                                    Cesantías
                                </div>

                                <div class="info-value">
                                    <?= $empleado['cesantias'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Caja compensación
                                </div>

                                <div class="info-value">
                                    <?= $empleado['caja_compensacion'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Nivel riesgo
                                </div>

                                <div class="info-value">
                                    <?= $empleado['nivel_riesgo'] ?>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="info-item">

                                <div class="info-label">
                                    Salario base
                                </div>

                                <div class="info-value">
                                    $<?= number_format($empleado['salario_base']) ?>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- CURSOS -->

        <div class="tab-pane fade"
        id="cursos">

            <?php

            $queryCursos = "

            SELECT
            empleado_cursos.*,
            cursos.nombre AS curso_nombre

            FROM empleado_cursos

            INNER JOIN cursos
            ON empleado_cursos.curso_id = cursos.id

            WHERE empleado_cursos.empleado_id = '$id'

            ORDER BY empleado_cursos.fecha_vencimiento ASC

            ";

            $resultCursos = mysqli_query($conn,$queryCursos);

            ?>

            <div class="card info-card shadow">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <h5>
                            Cursos y vencimientos
                        </h5>

                        <a href="agregar_curso.php?id=<?= $id ?>"
                        class="btn btn-primary">

                            + Agregar Curso

                        </a>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-dark">

                                <tr>

                                    <th>Curso</th>
                                    <th>Realización</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php while($curso = mysqli_fetch_assoc($resultCursos)){ ?>

                                <?php

                                $hoy = date('Y-m-d');

                                $vencimiento = $curso['fecha_vencimiento'];

                                $dias = (strtotime($vencimiento) - strtotime($hoy)) / 86400;

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
                                        <?= $curso['curso_nombre'] ?>
                                    </td>

                                    <td>
                                        <?= $curso['fecha_realizacion'] ?>
                                    </td>

                                    <td>
                                        <?= $curso['fecha_vencimiento'] ?>
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

        <!-- VACUNAS -->

        <div class="tab-pane fade"
        id="vacunas">

            <?php

            $queryVacunas = mysqli_query($conn,"

            SELECT * FROM vacunas
            WHERE empleado_id='$id'
            ORDER BY fecha_aplicacion DESC

            ");

            ?>

            <div class="card info-card shadow">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <h5>
                            Vacunas registradas
                        </h5>

                        <a href="agregar_vacuna.php?id=<?= $id ?>"
                        class="btn btn-primary">

                            + Agregar Vacuna

                        </a>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-dark">

                                <tr>

                                    <th>Vacuna</th>
                                    <th>Dosis</th>
                                    <th>Fecha</th>
                                    <th>Observaciones</th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php while($vacuna = mysqli_fetch_assoc($queryVacunas)){ ?>

                                <tr>

                                    <td>
                                        <?= $vacuna['tipo_vacuna'] ?>
                                    </td>

                                    <td>
                                        <?= $vacuna['dosis'] ?>
                                    </td>

                                    <td>
                                        <?= $vacuna['fecha_aplicacion'] ?>
                                    </td>

                                    <td>
                                        <?= $vacuna['observaciones'] ?>
                                    </td>

                                </tr>

                            <?php } ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>