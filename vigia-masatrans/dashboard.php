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

// ALERTAS CURSOS
$alertas_cursos = mysqli_query($conn,"
SELECT empleados.nombres, empleados.apellidos, empleados.id as emp_id,
cursos.nombre AS curso, empleado_cursos.fecha_vencimiento
FROM empleado_cursos
INNER JOIN empleados ON empleado_cursos.empleado_id = empleados.id
INNER JOIN cursos ON empleado_cursos.curso_id = cursos.id
WHERE empleados.estado = 'ACTIVO'
ORDER BY empleado_cursos.fecha_vencimiento ASC
LIMIT 50
");

// ALERTAS VACUNAS - verifica si vencio (1 año desde ultima dosis)
$alertas_vacunas = mysqli_query($conn,"
SELECT empleados.nombres, empleados.apellidos, empleados.id as emp_id,
vacunas_empleado.fv_fiebre_amarilla,
vacunas_empleado.esquema_dosis_1,
vacunas_empleado.esquema_dosis_2,
vacunas_empleado.esquema_dosis_3,
vacunas_empleado.covid_dosis_1,
vacunas_empleado.covid_dosis_2,
vacunas_empleado.updated_at
FROM vacunas_empleado
INNER JOIN empleados ON vacunas_empleado.empleado_id = empleados.id
WHERE empleados.estado = 'ACTIVO'
ORDER BY empleados.nombres ASC
");

// ALERTAS SARLAFT - vence 1 año desde fecha firma
$alertas_sarlaft = mysqli_query($conn,"
SELECT empleados.nombres, empleados.apellidos, empleados.id as emp_id,
antecedentes_empleado.fecha_sarlaft,
antecedentes_empleado.fecha_vencimiento_sarlaft,
antecedentes_empleado.pdf_policia,
antecedentes_empleado.pdf_procuraduria,
antecedentes_empleado.pdf_simit,
antecedentes_empleado.pdf_contraloria,
antecedentes_empleado.pdf_runt,
antecedentes_empleado.pdf_lista_clinton,
antecedentes_empleado.pdf_judicatura,
antecedentes_empleado.pdf_actualizacion
FROM empleados
LEFT JOIN antecedentes_empleado ON empleados.id = antecedentes_empleado.empleado_id
WHERE empleados.estado = 'ACTIVO'
ORDER BY empleados.nombres ASC
");

// ALERTAS REINDUCCION - vence 1 año desde fecha reinduccion
$alertas_reinduccion = mysqli_query($conn,"
SELECT empleados.nombres, empleados.apellidos, empleados.id as emp_id,
empleados.reinduccion,
DATE_ADD(empleados.reinduccion, INTERVAL 1 YEAR) as vencimiento_reinduccion
FROM empleados
WHERE empleados.estado = 'ACTIVO'
ORDER BY empleados.reinduccion ASC
");

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

<style>
.alerta-tab-btn {
    background:#e2e8f0;
    color:#334155;
    border:none;
    padding:10px 20px;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    font-size:13px;
    transition: all 0.2s;
    display:flex;
    align-items:center;
    gap:8px;
}
.alerta-tab-btn.active { background:#0a1628; color:white; }
.alerta-tab-btn:hover { opacity:0.85; }
.alerta-tab-btn .count-badge {
    background:rgba(255,255,255,0.25);
    padding:2px 8px;
    border-radius:99px;
    font-size:11px;
}
.alerta-tab-btn:not(.active) .count-badge {
    background:rgba(0,0,0,0.1);
}
.estado-vencido { color:#dc2626; font-weight:700; font-size:11px; }
.estado-por-vencer { color:#d97706; font-weight:700; font-size:11px; }
.estado-vigente { color:#059669; font-weight:700; font-size:11px; }
.estado-sin-registro { color:#94a3b8; font-weight:600; font-size:11px; }
</style>

<script>
function showAlerta(tab) {
    var tabs = ['cursos','vacunas','sarlaft','reinduccion'];
    for(var i = 0; i < tabs.length; i++){
        document.getElementById('alerta-' + tabs[i]).style.display = 'none';
        document.getElementById('abtn-' + tabs[i]).classList.remove('active');
    }
    document.getElementById('alerta-' + tab).style.display = 'block';
    document.getElementById('abtn-' + tab).classList.add('active');
}
</script>

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
        <div class="kpi-icon" style="background:rgba(99,102,241,0.10); width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px;">🚛</div>
      </div>
    </div>

    <div class="kpi-card" style="border-top:3px solid #0ea5e9;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="kpi-label">Área Administrativa</div>
          <div class="kpi-value" style="color:#0ea5e9;"><?= $administrativos ?></div>
          <div class="kpi-label mt-2">Personal de oficina y gestión</div>
        </div>
        <div class="kpi-icon" style="background:rgba(14,165,233,0.10); width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px;">💼</div>
      </div>
    </div>

  </section>

  <!-- ALERTAS CON PESTAÑAS -->
  <section class="panel alerts-table-wrap">
    <div class="panel-header">
      <div class="panel-title">🚨 Alertas Automáticas</div>
    </div>
    <div style="padding:16px 24px; border-bottom:1px solid #f1f5f9;">
      <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button class="alerta-tab-btn active" id="abtn-cursos" onclick="showAlerta('cursos')">
          📚 Cursos
          <span class="count-badge"><?= $cursos_vencidos + $cursos_por_vencer ?></span>
        </button>
        <button class="alerta-tab-btn" id="abtn-vacunas" onclick="showAlerta('vacunas')">
          💉 Vacunas
          <span class="count-badge"><?= $vacunas ?></span>
        </button>
        <button class="alerta-tab-btn" id="abtn-sarlaft" onclick="showAlerta('sarlaft')">
          🔍 SARLAFT / Antecedentes
          <span class="count-badge"><?= $empleados ?></span>
        </button>
        <button class="alerta-tab-btn" id="abtn-reinduccion" onclick="showAlerta('reinduccion')">
          🔄 Inducción / Reinducción
          <span class="count-badge"><?= $empleados ?></span>
        </button>
      </div>
    </div>

    <!-- TAB CURSOS -->
    <div id="alerta-cursos" class="table-wrapper">
      <table class="vigia-table">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Curso</th>
            <th>Vencimiento</th>
            <th>Estado</th>
            <th>Días</th>
          </tr>
        </thead>
        <tbody>
        <?php while($alerta = mysqli_fetch_assoc($alertas_cursos)){
            $hoy = date('Y-m-d');
            $dias = (strtotime($alerta['fecha_vencimiento']) - strtotime($hoy)) / 86400;
            if($dias < 0){
                $estado = "VENCIDO";
                $clase = "estado-vencido";
                $dias_txt = abs(round($dias)) . " días vencido";
            } elseif($dias <= 30){
                $estado = "POR VENCER";
                $clase = "estado-por-vencer";
                $dias_txt = round($dias) . " días restantes";
            } else {
                $estado = "VIGENTE";
                $clase = "estado-vigente";
                $dias_txt = round($dias) . " días restantes";
            }
        ?>
          <tr>
            <td>
              <a href="modules/empleados/perfil.php?id=<?= $alerta['emp_id'] ?>" style="color:#1a56db; font-weight:600; text-decoration:none;">
                <?= $alerta['nombres'] ?> <?= $alerta['apellidos'] ?>
              </a>
            </td>
            <td><?= $alerta['curso'] ?></td>
            <td><?= $alerta['fecha_vencimiento'] ?></td>
            <td><span class="<?= $clase ?>"><?= $estado ?></span></td>
            <td style="font-size:12px; color:#64748b;"><?= $dias_txt ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>

    <!-- TAB VACUNAS -->
    <div id="alerta-vacunas" style="display:none;" class="table-wrapper">
      <table class="vigia-table">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Fiebre Amarilla</th>
            <th>Última dosis esquema</th>
            <th>Última dosis COVID</th>
            <th>Estado general</th>
          </tr>
        </thead>
        <tbody>
        <?php while($vac = mysqli_fetch_assoc($alertas_vacunas)){
            $hoy = date('Y-m-d');

            // Última dosis esquema
            $dosis_esquema = array_filter([
                $vac['esquema_dosis_1'],
                $vac['esquema_dosis_2'],
                $vac['esquema_dosis_3'],
            ]);
            $ultima_esquema = !empty($dosis_esquema) ? max($dosis_esquema) : '';

            // Última dosis covid
            $dosis_covid = array_filter([
                $vac['covid_dosis_1'],
                $vac['covid_dosis_2'],
            ]);
            $ultima_covid = !empty($dosis_covid) ? max($dosis_covid) : '';

            // Estado fiebre amarilla
            $estado_fiebre = $vac['fv_fiebre_amarilla'] ? 'REGISTRADA' : 'SIN REGISTRO';
            $clase_fiebre = $vac['fv_fiebre_amarilla'] ? 'estado-vigente' : 'estado-sin-registro';

            // Estado general
            $tiene_todo = $vac['fv_fiebre_amarilla'] && $ultima_esquema && $ultima_covid;
            $estado_general = $tiene_todo ? 'COMPLETO' : 'INCOMPLETO';
            $clase_general = $tiene_todo ? 'estado-vigente' : 'estado-por-vencer';
        ?>
          <tr>
            <td>
              <a href="modules/empleados/perfil.php?id=<?= $vac['emp_id'] ?>" style="color:#1a56db; font-weight:600; text-decoration:none;">
                <?= $vac['nombres'] ?> <?= $vac['apellidos'] ?>
              </a>
            </td>
            <td><span class="<?= $clase_fiebre ?>"><?= $estado_fiebre ?></span></td>
            <td style="font-size:12px;"><?= $ultima_esquema ?: '<span class="estado-sin-registro">Sin registro</span>' ?></td>
            <td style="font-size:12px;"><?= $ultima_covid ?: '<span class="estado-sin-registro">Sin registro</span>' ?></td>
            <td><span class="<?= $clase_general ?>"><?= $estado_general ?></span></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>

    <!-- TAB SARLAFT -->
    <div id="alerta-sarlaft" style="display:none;" class="table-wrapper">
      <table class="vigia-table">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>SARLAFT Firmado</th>
            <th>Fecha firma</th>
            <th>Vence</th>
            <th>Estado SARLAFT</th>
            <th>Antecedentes</th>
          </tr>
        </thead>
        <tbody>
        <?php while($sar = mysqli_fetch_assoc($alertas_sarlaft)){
            $hoy = date('Y-m-d');

            // Estado SARLAFT
            if(empty($sar['fecha_sarlaft'])){
                $estado_sar = 'SIN REGISTRO';
                $clase_sar = 'estado-sin-registro';
                $vence_sar = '-';
            } else {
                $venc = $sar['fecha_vencimiento_sarlaft'];
                $dias_sar = (strtotime($venc) - strtotime($hoy)) / 86400;
                if($dias_sar < 0){
                    $estado_sar = 'VENCIDO';
                    $clase_sar = 'estado-vencido';
                } elseif($dias_sar <= 30){
                    $estado_sar = 'POR VENCER';
                    $clase_sar = 'estado-por-vencer';
                } else {
                    $estado_sar = 'VIGENTE';
                    $clase_sar = 'estado-vigente';
                }
                $vence_sar = $venc;
            }

            // Contar antecedentes completos
            $docs = ['pdf_policia','pdf_procuraduria','pdf_simit','pdf_contraloria','pdf_runt','pdf_lista_clinton','pdf_judicatura','pdf_actualizacion'];
            $completos = 0;
            foreach($docs as $d){ if(!empty($sar[$d])) $completos++; }
            $total_docs = count($docs);
            $clase_docs = $completos == $total_docs ? 'estado-vigente' : ($completos > 0 ? 'estado-por-vencer' : 'estado-sin-registro');
        ?>
          <tr>
            <td>
              <a href="modules/empleados/perfil.php?id=<?= $sar['emp_id'] ?>" style="color:#1a56db; font-weight:600; text-decoration:none;">
                <?= $sar['nombres'] ?> <?= $sar['apellidos'] ?>
              </a>
            </td>
            <td>
              <?php if($sar['fecha_sarlaft']){ ?>
                <span class="estado-vigente">✅ SI</span>
              <?php } else { ?>
                <span class="estado-sin-registro">❌ NO</span>
              <?php } ?>
            </td>
            <td style="font-size:12px;"><?= $sar['fecha_sarlaft'] ?: '-' ?></td>
            <td style="font-size:12px;"><?= $vence_sar ?></td>
            <td><span class="<?= $clase_sar ?>"><?= $estado_sar ?></span></td>
            <td><span class="<?= $clase_docs ?>"><?= $completos ?>/<?= $total_docs ?> documentos</span></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>

    <!-- TAB REINDUCCION -->
    <div id="alerta-reinduccion" style="display:none;" class="table-wrapper">
      <table class="vigia-table">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Fecha Inducción/Reinducción</th>
            <th>Vence</th>
            <th>Estado</th>
            <th>Días</th>
          </tr>
        </thead>
        <tbody>
        <?php while($rein = mysqli_fetch_assoc($alertas_reinduccion)){
            $hoy = date('Y-m-d');

            if(empty($rein['reinduccion']) || $rein['reinduccion'] == '0000-00-00'){
                $estado_r = 'SIN REGISTRO';
                $clase_r = 'estado-sin-registro';
                $vence_r = '-';
                $dias_txt_r = '-';
            } else {
                $vence_r = $rein['vencimiento_reinduccion'];
                $dias_r = (strtotime($vence_r) - strtotime($hoy)) / 86400;
                if($dias_r < 0){
                    $estado_r = 'VENCIDA';
                    $clase_r = 'estado-vencido';
                    $dias_txt_r = abs(round($dias_r)) . " días vencida";
                } elseif($dias_r <= 30){
                    $estado_r = 'POR VENCER';
                    $clase_r = 'estado-por-vencer';
                    $dias_txt_r = round($dias_r) . " días restantes";
                } else {
                    $estado_r = 'VIGENTE';
                    $clase_r = 'estado-vigente';
                    $dias_txt_r = round($dias_r) . " días restantes";
                }
            }
        ?>
          <tr>
            <td>
              <a href="modules/empleados/perfil.php?id=<?= $rein['emp_id'] ?>" style="color:#1a56db; font-weight:600; text-decoration:none;">
                <?= $rein['nombres'] ?> <?= $rein['apellidos'] ?>
              </a>
            </td>
            <td style="font-size:12px;"><?= $rein['reinduccion'] && $rein['reinduccion'] != '0000-00-00' ? $rein['reinduccion'] : '-' ?></td>
            <td style="font-size:12px;"><?= $vence_r ?></td>
            <td><span class="<?= $clase_r ?>"><?= $estado_r ?></span></td>
            <td style="font-size:12px; color:#64748b;"><?= $dias_txt_r ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>

  </section>

</div>

</body>
</html>