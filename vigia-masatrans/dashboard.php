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

$ind_vigentes = mysqli_num_rows(mysqli_query($conn,"
SELECT i.id FROM inducciones i
INNER JOIN empleados e ON i.empleado_id = e.id
WHERE e.estado = 'ACTIVO' AND i.fecha_vencimiento >= CURDATE()
"));

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

// ALERTAS VACUNAS
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

// ALERTAS SARLAFT
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

// ALERTAS INDUCCIÓN — lee de tabla inducciones real
$alertas_reinduccion = mysqli_query($conn,"
SELECT
    e.nombres, e.apellidos, e.id AS emp_id,
    i.fecha_realizacion,
    i.fecha_vencimiento,
    i.documento_pdf,
    i.observaciones
FROM empleados e
LEFT JOIN inducciones i ON e.id = i.empleado_id
WHERE e.estado = 'ACTIVO'
ORDER BY i.fecha_realizacion DESC, e.nombres ASC
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
.alerta-tab-btn:not(.active) .count-badge { background:rgba(0,0,0,0.1); }
.estado-vencido      { color:#dc2626; font-weight:700; font-size:11px; }
.estado-por-vencer   { color:#d97706; font-weight:700; font-size:11px; }
.estado-vigente      { color:#059669; font-weight:700; font-size:11px; }
.estado-sin-registro { color:#94a3b8; font-weight:600; font-size:11px; }

/* Botón PDF inducción */
.btn-ver-pdf {
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#fff1f2;
    color:#dc2626;
    border:1.5px solid #fecaca;
    border-radius:8px;
    padding:5px 11px;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
    text-decoration:none;
    transition:all 0.15s;
}
.btn-ver-pdf:hover {
    background:#fee2e2;
    border-color:#f87171;
    color:#b91c1c;
}
.btn-ver-pdf .pdf-ico {
    font-size:16px;
    line-height:1;
}

/* Modal visor PDF */
.modal-pdf .modal-content {
    border:none;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.25);
}
.modal-pdf .modal-header {
    background:#0a1628;
    color:white;
    padding:16px 22px;
    border:none;
}
.modal-pdf .modal-header .modal-title {
    font-size:15px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:10px;
}
.modal-pdf .btn-close {
    filter:invert(1);
    opacity:0.8;
}
.modal-pdf .modal-body {
    padding:0;
    background:#f1f5f9;
}
.modal-pdf iframe {
    width:100%;
    height:78vh;
    border:none;
    display:block;
}
.modal-pdf .modal-footer {
    background:#f8fafc;
    border-top:1px solid #e2e8f0;
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.modal-pdf .modal-footer small {
    color:#94a3b8;
    font-size:12px;
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
          <div class="kpi-label">Inducciones Vigentes</div>
          <div class="kpi-value"><?= $ind_vigentes ?></div>
        </div>
        <div class="kpi-icon">📋</div>
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
          <span class="count-badge"><?= $ind_vigentes ?></span>
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
            $dosis_esquema = array_filter([
                $vac['esquema_dosis_1'],
                $vac['esquema_dosis_2'],
                $vac['esquema_dosis_3'],
            ]);
            $ultima_esquema = !empty($dosis_esquema) ? max($dosis_esquema) : '';

            $dosis_covid = array_filter([
                $vac['covid_dosis_1'],
                $vac['covid_dosis_2'],
            ]);
            $ultima_covid = !empty($dosis_covid) ? max($dosis_covid) : '';

            $estado_fiebre = $vac['fv_fiebre_amarilla'] ? 'REGISTRADA' : 'SIN REGISTRO';
            $clase_fiebre  = $vac['fv_fiebre_amarilla'] ? 'estado-vigente' : 'estado-sin-registro';

            $tiene_todo    = $vac['fv_fiebre_amarilla'] && $ultima_esquema && $ultima_covid;
            $estado_general= $tiene_todo ? 'COMPLETO' : 'INCOMPLETO';
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

            if(empty($sar['fecha_sarlaft'])){
                $estado_sar = 'SIN REGISTRO';
                $clase_sar  = 'estado-sin-registro';
                $vence_sar  = '-';
            } else {
                $venc     = $sar['fecha_vencimiento_sarlaft'];
                $dias_sar = (strtotime($venc) - strtotime($hoy)) / 86400;
                if($dias_sar < 0){
                    $estado_sar = 'VENCIDO';
                    $clase_sar  = 'estado-vencido';
                } elseif($dias_sar <= 30){
                    $estado_sar = 'POR VENCER';
                    $clase_sar  = 'estado-por-vencer';
                } else {
                    $estado_sar = 'VIGENTE';
                    $clase_sar  = 'estado-vigente';
                }
                $vence_sar = $venc;
            }

            $docs = ['pdf_policia','pdf_procuraduria','pdf_simit','pdf_contraloria','pdf_runt','pdf_lista_clinton','pdf_judicatura','pdf_actualizacion'];
            $completos = 0;
            foreach($docs as $d){ if(!empty($sar[$d])) $completos++; }
            $total_docs = count($docs);
            $clase_docs = ($completos == $total_docs) ? 'estado-vigente' : ($completos > 0 ? 'estado-por-vencer' : 'estado-sin-registro');
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

    <!-- TAB INDUCCIÓN / REINDUCCIÓN -->
    <div id="alerta-reinduccion" style="display:none;" class="table-wrapper">
      <table class="vigia-table">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Fecha realización</th>
            <th>Fecha vencimiento</th>
            <th>Documento PDF</th>
            <th>Estado</th>
            <th>Días</th>
          </tr>
        </thead>
        <tbody>
        <?php while($rein = mysqli_fetch_assoc($alertas_reinduccion)){
            $hoy = date('Y-m-d');

            if(empty($rein['fecha_realizacion'])){
                $estado_r   = 'SIN REGISTRO';
                $clase_r    = 'estado-sin-registro';
                $vence_r    = '-';
                $dias_txt_r = '-';
            } else {
                $vence_r  = $rein['fecha_vencimiento'];
                $dias_r   = (strtotime($vence_r) - strtotime($hoy)) / 86400;
                if($dias_r < 0){
                    $estado_r   = 'VENCIDA';
                    $clase_r    = 'estado-vencido';
                    $dias_txt_r = abs(round($dias_r)) . " días vencida";
                } elseif($dias_r <= 30){
                    $estado_r   = 'POR VENCER';
                    $clase_r    = 'estado-por-vencer';
                    $dias_txt_r = round($dias_r) . " días restantes";
                } else {
                    $estado_r   = 'VIGENTE';
                    $clase_r    = 'estado-vigente';
                    $dias_txt_r = round($dias_r) . " días restantes";
                }
            }

            $nombre_empleado = htmlspecialchars($rein['nombres'] . ' ' . $rein['apellidos'], ENT_QUOTES);
            $pdf_url = !empty($rein['documento_pdf']) ? 'uploads/inducciones/' . $rein['documento_pdf'] : '';
        ?>
          <tr>
            <td>
              <a href="modules/empleados/perfil.php?id=<?= $rein['emp_id'] ?>" style="color:#1a56db; font-weight:600; text-decoration:none;">
                <?= $rein['nombres'] ?> <?= $rein['apellidos'] ?>
              </a>
            </td>
            <td style="font-size:12px;">
              <?= !empty($rein['fecha_realizacion']) ? date('d/m/Y', strtotime($rein['fecha_realizacion'])) : '-' ?>
            </td>
            <td style="font-size:12px;">
              <?= !empty($rein['fecha_vencimiento']) ? date('d/m/Y', strtotime($rein['fecha_vencimiento'])) : '-' ?>
            </td>
            <td>
              <?php if($pdf_url){ ?>
                <button class="btn-ver-pdf"
                  onclick="abrirPdfInduccion('<?= $pdf_url ?>', '<?= $nombre_empleado ?>')">
                  <span class="pdf-ico">📄</span>
                  Ver PDF
                </button>
              <?php } else { ?>
                <span class="estado-sin-registro">Sin documento</span>
              <?php } ?>
            </td>
            <td><span class="<?= $clase_r ?>"><?= $estado_r ?></span></td>
            <td style="font-size:12px; color:#64748b;"><?= $dias_txt_r ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>

  </section>

</div><!-- /main-content -->

<!-- ============ MODAL VISOR PDF INDUCCIÓN ============ -->
<div class="modal fade modal-pdf" id="modalVisorPdf" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">
          <span style="font-size:20px;">📄</span>
          <span id="pdf_modal_titulo">Documento de Inducción</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <iframe id="pdf_modal_iframe" src="" title="Visor PDF Inducción"></iframe>
      </div>
      <div class="modal-footer">
        <small>Si el PDF no carga correctamente, usa el botón para abrirlo en una nueva pestaña.</small>
        <a id="pdf_modal_link" href="#" target="_blank" class="btn btn-sm btn-outline-danger">
          🔗 Abrir en nueva pestaña
        </a>
      </div>
    </div>
  </div>
</div>
<!-- ==================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

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

function abrirPdfInduccion(url, nombre) {
    document.getElementById('pdf_modal_titulo').textContent = 'Inducción — ' + nombre;
    document.getElementById('pdf_modal_iframe').src = url;
    document.getElementById('pdf_modal_link').href = url;
    var modal = new bootstrap.Modal(document.getElementById('modalVisorPdf'));
    modal.show();
}

// Limpiar iframe al cerrar el modal para liberar memoria
document.getElementById('modalVisorPdf').addEventListener('hidden.bs.modal', function(){
    document.getElementById('pdf_modal_iframe').src = '';
});
</script>

</body>
</html>