<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

include("config/database.php");

$empleados = mysqli_num_rows(mysqli_query($conn,"
SELECT id FROM empleados WHERE estado='ACTIVO'
"));

$operativos = mysqli_num_rows(mysqli_query($conn,"
SELECT id FROM empleados WHERE area='OPERATIVO' AND estado='ACTIVO'
"));

$administrativos = mysqli_num_rows(mysqli_query($conn,"
SELECT id FROM empleados WHERE area='ADMINISTRATIVO' AND estado='ACTIVO'
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

$result_vacunas = mysqli_query($conn,"SELECT id FROM vacunas_empleado");
$vacunas = $result_vacunas ? mysqli_num_rows($result_vacunas) : 0;

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | VIGIA MASATRANS</title>
<link rel="icon" href="assets/img/logo.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">

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
    <a href="dashboard.php" class="active">
      <span class="nav-icon">📊</span>
      Dashboard
    </a>
    <a href="modules/empleados/empleados.php">
      <span class="nav-icon">👷</span>
      Empleados
    </a>
    <a href="logout.php">
      <span class="nav-icon">🚪</span>
      Cerrar sesión
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="dashboard.php">
      <span class="nav-icon">ℹ️</span>
      Control HSEQ
    </a>
  </div>
</div>

<!-- MAIN -->
<div class="main-content">

  <section class="page-header">
    <div class="page-header-left">
      <h1>Dashboard HSEQ</h1>
      <p>Panel ejecutivo para seguimiento de empleados, cursos y vacunas.</p>
    </div>
    <div class="page-header-actions">
      <a href="modules/empleados/empleados.php" class="btn btn-primary">Ver Empleados</a>
    </div>
  </section>

  <section class="profile-hero">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-4">
      <div>
        <h2>Visión general del cumplimiento</h2>
        <p class="text-muted">Monitorea los indicadores clave y las alertas para mantener la operación segura y certificada.</p>
      </div>
      <div class="avatar-lg"></div>
    </div>
  </section>

  <!-- KPI PRINCIPAL -->
  <section class="kpi-grid">

    <div class="kpi-card blue">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="kpi-label">Total Empleados Activos</div>
          <div class="kpi-value"><?= $empleados ?></div>
        </div>
        <div class="kpi-icon">👷</div>
      </div>
    </div>

    <div class="kpi-card red">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="kpi-label">Cursos Vencidos</div>
          <div class="kpi-value"><?= $cursos_vencidos ?></div>
        </div>
        <div class="kpi-icon">🚨</div>
      </div>
    </div>

    <div class="kpi-card amber">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="kpi-label">Por Vencer</div>
          <div class="kpi-value"><?= $cursos_por_vencer ?></div>
        </div>
        <div class="kpi-icon">⚠️</div>
      </div>
    </div>

    <div class="kpi-card green">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="kpi-label">Vacunas Registradas</div>
          <div class="kpi-value"><?= $vacunas ?></div>
        </div>
        <div class="kpi-icon">💉</div>
      </div>
    </div>

  </section>

  <!-- KPI AREAS -->
  <section style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:28px;">

    <div class="kpi-card" style="border-top:3px solid #6366f1;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="kpi-label">Área Operativa</div>
          <div class="kpi-value" style="color:#6366f1;"><?= $operativos ?></div>
          <div class="kpi-label mt-2">Conductores, Aparejadores y más</div>
        </div>
        <div class="kpi-icon" style="background:rgba(99,102,241,0.10); width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px;">
          🚛
        </div>
      </div>
    </div>

    <div class="kpi-card" style="border-top:3px solid #0ea5e9;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="kpi-label">Área Administrativa</div>
          <div class="kpi-value" style="color:#0ea5e9;"><?= $administrativos ?></div>
          <div class="kpi-label mt-2">Personal de oficina y gestión</div>
        </div>
        <div class="kpi-icon" style="background:rgba(14,165,233,0.10); width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px;">
          💼
        </div>
      </div>
    </div>

  </section>

  <!-- ALERTAS -->
  <section class="panel alerts-table-wrap">
    <div class="panel-header">
      <div class="panel-title">🚨 Alertas Automáticas</div>
      <span class="badge badge-warning">Últimas 20 alertas</span>
    </div>
    <div class="panel-body">
      <div class="table-wrapper">
        <table class="vigia-table">
          <thead>
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
          INNER JOIN empleados ON empleado_cursos.empleado_id = empleados.id
          INNER JOIN cursos ON empleado_cursos.curso_id = cursos.id
          ORDER BY empleado_cursos.fecha_vencimiento ASC
          LIMIT 20
          ";

          $resultadoAlertas = mysqli_query($conn, $queryAlertas);

          while($alerta = mysqli_fetch_assoc($resultadoAlertas)){

              $hoy = date('Y-m-d');
              $vencimiento = $alerta['fecha_vencimiento'];
              $dias = (strtotime($vencimiento) - strtotime($hoy)) / 86400;

              if($dias < 0){
                  $estado = "VENCIDO";
                  $badge = "badge-danger";
              } elseif($dias <= 30){
                  $estado = "POR VENCER";
                  $badge = "badge-warning";
              } else {
                  $estado = "VIGENTE";
                  $badge = "badge-success";
              }

          ?>

            <tr>
              <td><?= $alerta['nombres'] ?> <?= $alerta['apellidos'] ?></td>
              <td><?= $alerta['curso'] ?></td>
              <td><?= $alerta['fecha_vencimiento'] ?></td>
              <td><span class="badge <?= $badge ?>"><?= $estado ?></span></td>
            </tr>

          <?php } ?>

          </tbody>
        </table>
      </div>
    </div>
  </section>

</div>

</body>
</html>