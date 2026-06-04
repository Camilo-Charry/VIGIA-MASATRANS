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

$queryLicencia = "SELECT * FROM licencias WHERE empleado_id='$id' LIMIT 1";
$resultLicencia = mysqli_query($conn,$queryLicencia);
$licencia = mysqli_fetch_assoc($resultLicencia);

$queryCursos = "
SELECT empleado_cursos.*, cursos.nombre AS curso_nombre
FROM empleado_cursos
INNER JOIN cursos ON empleado_cursos.curso_id = cursos.id
WHERE empleado_cursos.empleado_id = '$id'
ORDER BY empleado_cursos.fecha_vencimiento ASC
";
$resultCursos = mysqli_query($conn,$queryCursos);

$resultVacunas = mysqli_query($conn,"SELECT * FROM vacunas_empleado WHERE empleado_id='$id'");

$resultDocumentos = mysqli_query($conn,"SELECT * FROM documentos_empleado WHERE empleado_id='$id' ORDER BY fecha_subida DESC");

?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil Empleado | VIGIA MASATRANS</title>

<link rel="icon" href="../../assets/img/logo.png" type="image/png">
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
    cursor:pointer;
    position:relative;
    overflow:hidden;
}
.profile-photo:hover .foto-overlay{ opacity:1; }
.foto-overlay{
    position:absolute;
    bottom:0; left:0; right:0;
    background:rgba(0,0,0,0.55);
    color:white;
    font-size:10px;
    text-align:center;
    padding:5px;
    opacity:0;
    transition: opacity 0.2s;
}
.info-card{ border:none; border-radius:20px; }
.info-item{ background:#f8fafc; border-radius:15px; padding:20px; height:100%; }
.info-label{ color:#64748b; font-size:14px; }
.info-value{ font-weight:600; font-size:16px; color:#0f172a; }
.tab-btn{
    background:#e2e8f0;
    color:#334155;
    border:none;
    padding:10px 20px;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    font-size:14px;
    transition: all 0.2s;
}
.tab-btn.active{ background:#2563eb; color:white; }
.tab-btn:hover{ opacity: 0.85; }

#modalPDF {
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.85);
    z-index:9999;
    align-items:center;
    justify-content:center;
}
#modalPDF.open { display:flex; }
.modal-pdf-box {
    background:white;
    width:85%;
    height:90vh;
    border-radius:16px;
    overflow:hidden;
    position:relative;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.modal-pdf-header {
    background:#0a1628;
    color:white;
    padding:14px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-weight:600;
}
.modal-pdf-close {
    background:#ef4444;
    color:white;
    border:none;
    border-radius:8px;
    padding:6px 14px;
    cursor:pointer;
    font-weight:600;
}
</style>

<script>
function showTab(tab) {
    var tabs = ['general','laboral','licencia','seguridad','cursos','vacunas','documentos'];
    for(var i = 0; i < tabs.length; i++){
        document.getElementById('tab-' + tabs[i]).style.display = 'none';
        document.getElementById('btn-' + tabs[i]).classList.remove('active');
    }
    document.getElementById('tab-' + tab).style.display = 'block';
    document.getElementById('btn-' + tab).classList.add('active');
}

function verPDF(archivo, carpeta) {
    var ruta = carpeta ? '../../uploads/' + carpeta + '/' + archivo : '../../uploads/cursos/' + archivo;
    document.getElementById('iframePDF').src = ruta;
    document.getElementById('modalPDF').classList.add('open');
}

function cerrarPDF() {
    document.getElementById('modalPDF').classList.remove('open');
    document.getElementById('iframePDF').src = '';
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

<!-- MODAL PDF -->
<div id="modalPDF">
    <div class="modal-pdf-box">
        <div class="modal-pdf-header">
            <span>📄 Documento</span>
            <button class="modal-pdf-close" onclick="cerrarPDF()">✕ Cerrar</button>
        </div>
        <iframe id="iframePDF" src="" width="100%" height="100%" style="border:none;"></iframe>
    </div>
</div>

<!-- MAIN -->
<div class="main-content">

    <!-- HEADER -->
    <div class="profile-header shadow mb-4">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">

                <div class="profile-photo" onclick="document.getElementById('inputFoto').click()">
                    <?php if($empleado['foto']){ ?>
                        <img src="../../uploads/fotos/<?= $empleado['foto'] ?>"
                             style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                    <?php } else { ?>
                        <?= strtoupper(substr($empleado['nombres'],0,1)) ?>
                    <?php } ?>
                    <div class="foto-overlay">📷 Cambiar</div>
                </div>

                <form method="POST" action="subir_foto.php" enctype="multipart/form-data" id="formFoto">
                    <input type="hidden" name="empleado_id" value="<?= $id ?>">
                    <input type="file" id="inputFoto" name="foto" accept="image/*" style="display:none"
                           onchange="document.getElementById('formFoto').submit()">
                </form>

            </div>
            <div class="col-md-10">
                <h2 class="fw-bold">
                    <?= $empleado['nombres'] ?>
                    <?= $empleado['apellidos'] ?>
                </h2>
                <p class="mb-1"><?= $empleado['cargo'] ?></p>
                <small>Cédula: <?= $empleado['cedula'] ?></small>
                <div class="mt-3">

                    <?php if($empleado['rol_conductor'] == 'SI'){ ?>
                        <span class="badge bg-warning text-dark">Conductor</span>
                    <?php } ?>

                    <?php if($empleado['estado'] == 'ACTIVO'){ ?>
                        <span class="badge bg-success">ACTIVO</span>
                        <a href="cambiar_estado.php?id=<?= $id ?>&estado=INACTIVO"
                        class="btn btn-sm btn-warning ms-2"
                        onclick="return confirm('¿Desactivar este empleado?')">
                            ⏸ Desactivar
                        </a>
                        <a href="cambiar_estado.php?id=<?= $id ?>&estado=RETIRADO"
                        class="btn btn-sm btn-danger ms-2"
                        onclick="return confirm('¿Marcar como retirado?')">
                            🚪 Retirar
                        </a>
                    <?php } elseif($empleado['estado'] == 'INACTIVO'){ ?>
                        <span class="badge bg-secondary">INACTIVO</span>
                        <a href="cambiar_estado.php?id=<?= $id ?>&estado=ACTIVO"
                        class="btn btn-sm btn-success ms-2"
                        onclick="return confirm('¿Activar este empleado?')">
                            ▶ Activar
                        </a>
                    <?php } elseif($empleado['estado'] == 'RETIRADO'){ ?>
                        <span class="badge bg-danger">RETIRADO</span>
                        <a href="cambiar_estado.php?id=<?= $id ?>&estado=ACTIVO"
                        class="btn btn-sm btn-success ms-2"
                        onclick="return confirm('¿Reactivar este empleado?')">
                            ▶ Reactivar
                        </a>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div style="display:flex; gap:8px; flex-wrap:wrap; margin: 24px 0;">
        <button class="tab-btn active" id="btn-general" onclick="showTab('general')">👤 General</button>
        <button class="tab-btn" id="btn-laboral" onclick="showTab('laboral')">🚛 Laboral</button>
        <button class="tab-btn" id="btn-licencia" onclick="showTab('licencia')">🚘 Licencia</button>
        <button class="tab-btn" id="btn-seguridad" onclick="showTab('seguridad')">🏥 Seguridad Social</button>
        <button class="tab-btn" id="btn-cursos" onclick="showTab('cursos')">📚 Cursos</button>
        <button class="tab-btn" id="btn-vacunas" onclick="showTab('vacunas')">💉 Vacunas</button>
        <button class="tab-btn" id="btn-documentos" onclick="showTab('documentos')">📁 Documentos</button>
    </div>

    <!-- GENERAL -->
    <div id="tab-general">
        <div class="card info-card shadow">
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">RH</div>
                            <div class="info-value"><?= $empleado['rh'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Fecha nacimiento</div>
                            <div class="info-value"><?= $empleado['fecha_nacimiento'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Género</div>
                            <div class="info-value"><?= $empleado['genero'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Dirección</div>
                            <div class="info-value"><?= $empleado['direccion'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-item">
                            <div class="info-label">Ciudad</div>
                            <div class="info-value"><?= $empleado['ciudad_residencia'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-item">
                            <div class="info-label">Departamento</div>
                            <div class="info-value"><?= $empleado['departamento_residencia'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Celular</div>
                            <div class="info-value"><?= $empleado['celular'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Correo</div>
                            <div class="info-value"><?= $empleado['correo'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Nivel Académico</div>
                            <div class="info-value"><?= $empleado['nivel_academico'] ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- LABORAL -->
    <div id="tab-laboral" style="display:none;">
        <div class="card info-card shadow">
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Cargo</div>
                            <div class="info-value"><?= $empleado['cargo'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Fecha ingreso</div>
                            <div class="info-value"><?= $empleado['fecha_ingreso'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Turno</div>
                            <div class="info-value"><?= $empleado['turno_trabajo'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Contrato</div>
                            <div class="info-value"><?= $empleado['tipo_contrato'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Base operación</div>
                            <div class="info-value"><?= $empleado['base_operacion'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">SIPLAFT</div>
                            <div class="info-value"><?= $empleado['siplaft'] ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- LICENCIA -->
    <div id="tab-licencia" style="display:none;">
        <div class="card info-card shadow">
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Categoría</div>
                            <div class="info-value"><?= $licencia['categoria'] ?? 'Sin registro' ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Vencimiento</div>
                            <div class="info-value"><?= $licencia['fecha_vencimiento'] ?? 'Sin registro' ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Restricciones</div>
                            <div class="info-value"><?= $licencia['restricciones'] ?? 'Sin registro' ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- SEGURIDAD SOCIAL -->
    <div id="tab-seguridad" style="display:none;">
        <div class="card info-card shadow">
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-md-3">
                        <div class="info-item">
                            <div class="info-label">EPS</div>
                            <div class="info-value"><?= $empleado['eps'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-item">
                            <div class="info-label">ARL</div>
                            <div class="info-value"><?= $empleado['arl'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-item">
                            <div class="info-label">Pensión</div>
                            <div class="info-value"><?= $empleado['pension'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-item">
                            <div class="info-label">Cesantías</div>
                            <div class="info-value"><?= $empleado['cesantias'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Caja compensación</div>
                            <div class="info-value"><?= $empleado['caja_compensacion'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Nivel riesgo</div>
                            <div class="info-value"><?= $empleado['nivel_riesgo'] ?></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Salario base</div>
                            <div class="info-value">$<?= number_format($empleado['salario_base']) ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- CURSOS -->
    <div id="tab-cursos" style="display:none;">
        <div class="card info-card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>📚 Cursos y vencimientos</h5>
                    <a href="agregar_curso.php?id=<?= $id ?>" class="btn btn-primary">+ Agregar Curso</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Curso</th>
                                <th>Realización</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Soporte</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($curso = mysqli_fetch_assoc($resultCursos)){
                            $hoy = date('Y-m-d');
                            $dias = (strtotime($curso['fecha_vencimiento']) - strtotime($hoy)) / 86400;
                            if($dias < 0){ $estado = "VENCIDO"; $badge = "danger"; }
                            elseif($dias <= 30){ $estado = "POR VENCER"; $badge = "warning"; }
                            else { $estado = "VIGENTE"; $badge = "success"; }
                        ?>
                            <tr>
                                <td><strong><?= $curso['curso_nombre'] ?></strong></td>
                                <td><?= $curso['fecha_realizacion'] ?></td>
                                <td><?= $curso['fecha_vencimiento'] ?></td>
                                <td><span class="badge bg-<?= $badge ?>"><?= $estado ?></span></td>
                                <td>
                                    <?php if($curso['pdf_soporte']){ ?>
                                        <button onclick="verPDF('<?= $curso['pdf_soporte'] ?>','cursos')" class="btn btn-sm btn-primary">
                                            📄 Ver PDF
                                        </button>
                                    <?php } else { ?>
                                        <span class="text-muted">Sin soporte</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="renovar_curso.php?curso_id=<?= $curso['id'] ?>&empleado_id=<?= $id ?>"
                                        class="btn btn-sm btn-warning">🔄 Renovar</a>
                                        <a href="eliminar_curso.php?curso_id=<?= $curso['id'] ?>&empleado_id=<?= $id ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Eliminar este curso?')">🗑️ Eliminar</a>
                                    </div>
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
<div id="tab-vacunas" style="display:none;">
    <div class="card info-card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5>💉 Vacunas registradas</h5>
                <div class="d-flex gap-2">
                    <a href="agregar_vacuna.php?id=<?= $id ?>" class="btn btn-primary">
                        ✏️ Registrar / Editar Vacunas
                    </a>
                    <?php if($resultVacunas && mysqli_num_rows(mysqli_query($conn,"SELECT id FROM vacunas_empleado WHERE empleado_id='$id'")) > 0){ ?>
                        <a href="eliminar_vacuna.php?empleado_id=<?= $id ?>"
                        class="btn btn-danger"
                        onclick="return confirm('¿Eliminar todo el registro de vacunas?')">
                            🗑️ Eliminar
                        </a>
                    <?php } ?>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Vacuna</th>
                            <th>Dosis 1</th>
                            <th>Dosis 2</th>
                            <th>Dosis 3</th>
                            <th>Dosis 4</th>
                            <th>Dosis 5</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $resultVacunas2 = mysqli_query($conn,"SELECT * FROM vacunas_empleado WHERE empleado_id='$id'");
                    if($resultVacunas2){ while($vacuna = mysqli_fetch_assoc($resultVacunas2)){ ?>
                        <tr>
                            <td><strong>Fiebre Amarilla</strong></td>
                            <td colspan="5"><?= $vacuna['fv_fiebre_amarilla'] ?: '-' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Esquema</strong></td>
                            <td><?= $vacuna['esquema_dosis_1'] ?: '-' ?></td>
                            <td><?= $vacuna['esquema_dosis_2'] ?: '-' ?></td>
                            <td><?= $vacuna['esquema_dosis_3'] ?: '-' ?></td>
                            <td><?= $vacuna['esquema_dosis_4'] ?: '-' ?></td>
                            <td><?= $vacuna['esquema_dosis_5'] ?: '-' ?></td>
                        </tr>
                        <tr>
                            <td><strong>COVID-19</strong></td>
                            <td><?= $vacuna['covid_dosis_1'] ?: '-' ?></td>
                            <td><?= $vacuna['covid_dosis_2'] ?: '-' ?></td>
                            <td><?= $vacuna['covid_dosis_3'] ?: '-' ?></td>
                            <td><?= $vacuna['covid_dosis_4'] ?: '-' ?></td>
                            <td>-</td>
                        </tr>
                        <?php if($vacuna['observaciones']){ ?>
                        <tr>
                            <td><strong>Observaciones</strong></td>
                            <td colspan="5"><?= $vacuna['observaciones'] ?></td>
                        </tr>
                        <?php } ?>
                        <?php if($vacuna['pdf_vacuna']){ ?>
                        <tr>
                            <td><strong>Certificado</strong></td>
                            <td colspan="5">
                                <button onclick="verPDF('<?= $vacuna['pdf_vacuna'] ?>','vacunas')"
                                class="btn btn-sm btn-primary">
                                    📄 Ver PDF
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    <!-- DOCUMENTOS -->
    <div id="tab-documentos" style="display:none;">
        <div class="card info-card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>📁 Documentos del empleado</h5>
                    <a href="subir_documento.php?id=<?= $id ?>" class="btn btn-primary">+ Subir Documento</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Fecha subida</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($resultDocumentos){ while($doc = mysqli_fetch_assoc($resultDocumentos)){ ?>
                            <tr>
                                <td><span class="badge bg-primary"><?= $doc['tipo_documento'] ?></span></td>
                                <td><?= $doc['descripcion'] ? $doc['descripcion'] : '<span class="text-muted">Sin descripción</span>' ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($doc['fecha_subida'])) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <?php
                                        $ext = strtolower(pathinfo($doc['nombre_archivo'], PATHINFO_EXTENSION));
                                        if(in_array($ext, ['pdf','jpg','jpeg','png'])){
                                        ?>
                                            <button onclick="verPDF('<?= $doc['nombre_archivo'] ?>','documentos')"
                                            class="btn btn-sm btn-primary">
                                                👁️ Ver
                                            </button>
                                        <?php } ?>
                                        <a href="../../uploads/documentos/<?= $doc['nombre_archivo'] ?>"
                                        download class="btn btn-sm btn-success">
                                            ⬇️ Descargar
                                        </a>
                                        <a href="eliminar_documento.php?doc_id=<?= $doc['id'] ?>&empleado_id=<?= $id ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Eliminar este documento?')">
                                            🗑️ Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>