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

$resultAntecedentes = mysqli_query($conn,"SELECT * FROM antecedentes_empleado WHERE empleado_id='$id' LIMIT 1");
$antecedentes = mysqli_fetch_assoc($resultAntecedentes);

// URL del perfil para el QR
$url_perfil = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . '/vigia-masatrans/modules/empleados/perfil.php?id=' . $id;

// Foto en base64 para el carnet (para que funcione al imprimir/descargar)
$foto_base64 = '';
if(!empty($empleado['foto'])){
    $ruta_foto = '../../uploads/fotos/' . $empleado['foto'];
    if(file_exists($ruta_foto)){
        $tipo = mime_content_type($ruta_foto);
        $foto_base64 = 'data:' . $tipo . ';base64,' . base64_encode(file_get_contents($ruta_foto));
    }
}

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
.doc-item{
    background:#f8fafc;
    border-radius:12px;
    padding:16px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
    border:1px solid #e2e8f0;
}
.doc-item-info{ font-weight:600; color:#0f172a; font-size:14px; }
.doc-item-sub{ color:#64748b; font-size:12px; }

/* ===== MODAL CARNET ===== */
#modalCarnet {
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.75);
    z-index:9998;
    align-items:center;
    justify-content:center;
    backdrop-filter: blur(4px);
}
#modalCarnet.open { display:flex; }

.carnet-wrapper {
    background:white;
    border-radius:20px;
    padding:28px;
    max-width:520px;
    width:95%;
    box-shadow: 0 30px 80px rgba(0,0,0,0.4);
    display:flex;
    flex-direction:column;
    gap:20px;
}

.carnet-actions {
    display:flex;
    gap:10px;
    justify-content:flex-end;
}

.btn-carnet-imprimir {
    background:#0f172a;
    color:white;
    border:none;
    border-radius:10px;
    padding:9px 20px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:8px;
    transition:all 0.15s;
}
.btn-carnet-imprimir:hover { background:#1e293b; }

.btn-carnet-descargar {
    background:#2563eb;
    color:white;
    border:none;
    border-radius:10px;
    padding:9px 20px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:8px;
    transition:all 0.15s;
}
.btn-carnet-descargar:hover { background:#1d4ed8; }

.btn-carnet-cerrar {
    background:#f1f5f9;
    color:#334155;
    border:none;
    border-radius:10px;
    padding:9px 20px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    transition:all 0.15s;
}
.btn-carnet-cerrar:hover { background:#e2e8f0; }

/* ===== DISEÑO DEL CARNET ===== */
#carnet-digital {
    width:100%;
    background:white;
    border-radius:16px;
    overflow:hidden;
    border:1px solid #e2e8f0;
    font-family: 'Segoe UI', Arial, sans-serif;
}

.carnet-top {
    background:linear-gradient(135deg, #0f172a 0%, #1e40af 100%);
    padding:18px 22px 14px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.carnet-logo-area {
    display:flex;
    align-items:center;
    gap:12px;
}

.carnet-logo-circle {
    width:40px; height:40px;
    border-radius:50%;
    background:rgba(255,255,255,0.15);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    border:2px solid rgba(255,255,255,0.3);
}

.carnet-empresa {
    color:white;
}
.carnet-empresa strong {
    display:block;
    font-size:13px;
    letter-spacing:0.5px;
}
.carnet-empresa span {
    font-size:10px;
    color:rgba(255,255,255,0.65);
    letter-spacing:1px;
    text-transform:uppercase;
}

.carnet-tipo-badge {
    background:rgba(255,255,255,0.15);
    color:white;
    font-size:10px;
    font-weight:700;
    padding:4px 12px;
    border-radius:99px;
    letter-spacing:1px;
    text-transform:uppercase;
    border:1px solid rgba(255,255,255,0.25);
}

.carnet-body {
    padding:22px;
    display:flex;
    gap:20px;
    align-items:flex-start;
}

.carnet-foto-col {
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:10px;
    flex-shrink:0;
}

.carnet-foto {
    width:90px; height:90px;
    border-radius:12px;
    object-fit:cover;
    border:3px solid #e2e8f0;
    background:#dbeafe;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    font-weight:700;
    color:#1d4ed8;
    overflow:hidden;
}
.carnet-foto img { width:100%; height:100%; object-fit:cover; border-radius:9px; }

.carnet-qr-box {
    width:72px; height:72px;
    border-radius:8px;
    overflow:hidden;
    border:2px solid #e2e8f0;
    background:#f8fafc;
    display:flex;
    align-items:center;
    justify-content:center;
}
.carnet-qr-box img { width:100%; height:100%; display:block; }

.carnet-qr-label {
    font-size:9px;
    color:#94a3b8;
    text-align:center;
    letter-spacing:0.5px;
}

.carnet-info-col {
    flex:1;
}

.carnet-nombre {
    font-size:17px;
    font-weight:700;
    color:#0f172a;
    line-height:1.2;
    margin-bottom:2px;
}

.carnet-cargo {
    font-size:12px;
    color:#2563eb;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:0.5px;
    margin-bottom:14px;
}

.carnet-fields {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px;
}

.carnet-field {
    background:#f8fafc;
    border-radius:8px;
    padding:8px 10px;
}

.carnet-field-label {
    font-size:9px;
    color:#94a3b8;
    text-transform:uppercase;
    letter-spacing:0.5px;
    margin-bottom:2px;
}

.carnet-field-value {
    font-size:12px;
    font-weight:600;
    color:#1e293b;
}

.carnet-bottom {
    background:#f8fafc;
    border-top:1px solid #e2e8f0;
    padding:10px 22px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.carnet-bottom-left {
    font-size:10px;
    color:#94a3b8;
}

.carnet-estado-badge {
    font-size:10px;
    font-weight:700;
    padding:4px 12px;
    border-radius:99px;
    letter-spacing:0.5px;
}
.carnet-estado-activo { background:#d1fae5; color:#065f46; }
.carnet-estado-inactivo { background:#f1f5f9; color:#475569; }
.carnet-estado-retirado { background:#fee2e2; color:#991b1b; }

/* ===== PRINT ===== */
@media print {
    body * { visibility: hidden !important; }
    #carnet-digital, #carnet-digital * { visibility: visible !important; }
    #carnet-digital {
        position: fixed !important;
        top: 0; left: 0;
        width: 9cm !important;
        margin: auto;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
}
</style>

<script>
function showTab(tab) {
    var tabs = ['general','laboral','licencia','seguridad','cursos','vacunas','documentos','antecedentes'];
    for(var i = 0; i < tabs.length; i++){
        document.getElementById('tab-' + tabs[i]).style.display = 'none';
        document.getElementById('btn-' + tabs[i]).classList.remove('active');
    }
    document.getElementById('tab-' + tab).style.display = 'block';
    document.getElementById('btn-' + tab).classList.add('active');
}

function verPDF(archivo, carpeta) {
    var ruta = '../../uploads/' + carpeta + '/' + archivo;
    document.getElementById('iframePDF').src = ruta;
    document.getElementById('modalPDF').classList.add('open');
}

function cerrarPDF() {
    document.getElementById('modalPDF').classList.remove('open');
    document.getElementById('iframePDF').src = '';
}

function abrirCarnet() {
    document.getElementById('modalCarnet').classList.add('open');
}

function cerrarCarnet() {
    document.getElementById('modalCarnet').classList.remove('open');
}

function imprimirCarnet() {
    window.print();
}

function descargarCarnet() {
    var btn = document.querySelector('.btn-carnet-descargar');
    btn.textContent = '⏳ Generando...';
    btn.disabled = true;

    html2canvas(document.getElementById('carnet-digital'), {
        scale: 3,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff'
    }).then(function(canvas) {
        var link = document.createElement('a');
        link.download = 'carnet_<?= $empleado['cedula'] ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        btn.innerHTML = '⬇️ Descargar imagen';
        btn.disabled = false;
    }).catch(function(){
        btn.innerHTML = '⬇️ Descargar imagen';
        btn.disabled = false;
        alert('Error al generar imagen. Intenta imprimir en su lugar.');
    });
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

<!-- MODAL PDF DOCUMENTOS -->
<div id="modalPDF">
    <div class="modal-pdf-box">
        <div class="modal-pdf-header">
            <span>📄 Documento</span>
            <button class="modal-pdf-close" onclick="cerrarPDF()">✕ Cerrar</button>
        </div>
        <iframe id="iframePDF" src="" width="100%" height="100%" style="border:none;"></iframe>
    </div>
</div>

<!-- MODAL CARNET DIGITAL -->
<div id="modalCarnet">
    <div class="carnet-wrapper">

        <!-- Acciones -->
        <div class="carnet-actions">
            <button class="btn-carnet-cerrar" onclick="cerrarCarnet()">✕ Cerrar</button>
            <button class="btn-carnet-imprimir" onclick="imprimirCarnet()">🖨️ Imprimir</button>
            <button class="btn-carnet-descargar" onclick="descargarCarnet()">⬇️ Descargar imagen</button>
        </div>

        <!-- CARNET -->
        <div id="carnet-digital">

            <!-- Encabezado -->
            <div class="carnet-top">
                <div class="carnet-logo-area">
                    <div class="carnet-logo-circle">🚛</div>
                    <div class="carnet-empresa">
                        <strong>MASATRANS</strong>
                        <span>Identificación HSEQ</span>
                    </div>
                </div>
                <div class="carnet-tipo-badge">
                    <?= strtoupper($empleado['area'] ?? 'Personal') ?>
                </div>
            </div>

            <!-- Cuerpo -->
            <div class="carnet-body">

                <!-- Columna foto + QR -->
                <div class="carnet-foto-col">
                    <div class="carnet-foto">
                        <?php if($foto_base64){ ?>
                            <img src="<?= $foto_base64 ?>" alt="Foto empleado">
                        <?php } else { ?>
                            <?= strtoupper(substr($empleado['nombres'],0,1)) ?>
                        <?php } ?>
                    </div>

                    <!-- QR generado con API pública -->
                    <div class="carnet-qr-box">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($url_perfil) ?>"
                             alt="QR Perfil" crossorigin="anonymous">
                    </div>
                    <div class="carnet-qr-label">Escanear perfil</div>
                </div>

                <!-- Columna datos -->
                <div class="carnet-info-col">
                    <div class="carnet-nombre">
                        <?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?>
                    </div>
                    <div class="carnet-cargo"><?= $empleado['cargo'] ?></div>

                    <div class="carnet-fields">
                        <div class="carnet-field">
                            <div class="carnet-field-label">Cédula</div>
                            <div class="carnet-field-value"><?= $empleado['cedula'] ?></div>
                        </div>
                        <div class="carnet-field">
                            <div class="carnet-field-label">RH</div>
                            <div class="carnet-field-value"><?= $empleado['rh'] ?: '—' ?></div>
                        </div>
                        <div class="carnet-field">
                            <div class="carnet-field-label">ARL</div>
                            <div class="carnet-field-value"><?= $empleado['arl'] ?: '—' ?></div>
                        </div>
                        <div class="carnet-field">
                            <div class="carnet-field-label">Ingreso</div>
                            <div class="carnet-field-value">
                                <?= $empleado['fecha_ingreso'] ? date('d/m/Y', strtotime($empleado['fecha_ingreso'])) : '—' ?>
                            </div>
                        </div>
                        <div class="carnet-field" style="grid-column:1/3;">
                            <div class="carnet-field-label">EPS</div>
                            <div class="carnet-field-value"><?= $empleado['eps'] ?: '—' ?></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Pie del carnet -->
            <div class="carnet-bottom">
                <div class="carnet-bottom-left">
                    VIGIA MASATRANS · Sistema HSEQ
                </div>
                <?php
                $estadoClase = 'carnet-estado-activo';
                if($empleado['estado'] == 'INACTIVO') $estadoClase = 'carnet-estado-inactivo';
                if($empleado['estado'] == 'RETIRADO') $estadoClase = 'carnet-estado-retirado';
                ?>
                <span class="carnet-estado-badge <?= $estadoClase ?>">
                    <?= $empleado['estado'] ?>
                </span>
            </div>

        </div>
        <!-- /carnet-digital -->

    </div>
</div>
<!-- /modal carnet -->

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
                <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                    <?php if($empleado['rol_conductor'] == 'SI'){ ?>
                        <span class="badge bg-warning text-dark">Conductor</span>
                    <?php } ?>
                    <?php if($empleado['area'] ?? ''){ ?>
                        <span class="badge bg-info text-dark"><?= $empleado['area'] ?></span>
                    <?php } ?>
                    <?php if($empleado['estado'] == 'ACTIVO'){ ?>
                        <span class="badge bg-success">ACTIVO</span>
                        <a href="cambiar_estado.php?id=<?= $id ?>&estado=INACTIVO"
                        class="btn btn-sm btn-warning"
                        onclick="return confirm('¿Desactivar este empleado?')">⏸ Desactivar</a>
                        <a href="cambiar_estado.php?id=<?= $id ?>&estado=RETIRADO"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('¿Marcar como retirado?')">🚪 Retirar</a>
                    <?php } elseif($empleado['estado'] == 'INACTIVO'){ ?>
                        <span class="badge bg-secondary">INACTIVO</span>
                        <a href="cambiar_estado.php?id=<?= $id ?>&estado=ACTIVO"
                        class="btn btn-sm btn-success"
                        onclick="return confirm('¿Activar este empleado?')">▶ Activar</a>
                    <?php } elseif($empleado['estado'] == 'RETIRADO'){ ?>
                        <span class="badge bg-danger">RETIRADO</span>
                        <a href="cambiar_estado.php?id=<?= $id ?>&estado=ACTIVO"
                        class="btn btn-sm btn-success"
                        onclick="return confirm('¿Reactivar este empleado?')">▶ Reactivar</a>
                    <?php } ?>

                    <!-- BOTÓN CARNET -->
                    <button onclick="abrirCarnet()"
                        style="background:linear-gradient(135deg,#2563eb,#1d4ed8); color:white; border:none; border-radius:10px; padding:7px 18px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 14px rgba(37,99,235,0.4);">
                        🪪 Carnet Digital
                    </button>
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
        <button class="tab-btn" id="btn-antecedentes" onclick="showTab('antecedentes')">🔍 Antecedentes</button>
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
                            <div class="info-label">Área</div>
                            <div class="info-value"><?= $empleado['area'] ?? 'Sin asignar' ?></div>
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
                                        <button onclick="verPDF('<?= $curso['pdf_soporte'] ?>','cursos')" class="btn btn-sm btn-primary">📄 Ver PDF</button>
                                    <?php } else { ?>
                                        <span class="text-muted">Sin soporte</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="renovar_curso.php?curso_id=<?= $curso['id'] ?>&empleado_id=<?= $id ?>" class="btn btn-sm btn-warning">🔄 Renovar</a>
                                        <a href="eliminar_curso.php?curso_id=<?= $curso['id'] ?>&empleado_id=<?= $id ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este curso?')">🗑️ Eliminar</a>
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
                        <a href="agregar_vacuna.php?id=<?= $id ?>" class="btn btn-primary">✏️ Registrar / Editar</a>
                        <?php
                        $countVac = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM vacunas_empleado WHERE empleado_id='$id'"));
                        if($countVac > 0){ ?>
                            <a href="eliminar_vacuna.php?empleado_id=<?= $id ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar registro de vacunas?')">🗑️ Eliminar</a>
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
                                    <button onclick="verPDF('<?= $vacuna['pdf_vacuna'] ?>','vacunas')" class="btn btn-sm btn-primary">📄 Ver PDF</button>
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
                                        if(in_array($ext, ['pdf','jpg','jpeg','png'])){ ?>
                                            <button onclick="verPDF('<?= $doc['nombre_archivo'] ?>','documentos')" class="btn btn-sm btn-primary">👁️ Ver</button>
                                        <?php } ?>
                                        <a href="../../uploads/documentos/<?= $doc['nombre_archivo'] ?>" download class="btn btn-sm btn-success">⬇️ Descargar</a>
                                        <a href="eliminar_documento.php?doc_id=<?= $doc['id'] ?>&empleado_id=<?= $id ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este documento?')">🗑️ Eliminar</a>
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

    <!-- ANTECEDENTES -->
    <div id="tab-antecedentes" style="display:none;">
        <div class="card info-card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>🔍 Antecedentes y SARLAFT</h5>
                    <a href="antecedentes.php?id=<?= $id ?>" class="btn btn-primary">✏️ Gestionar Antecedentes</a>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">SARLAFT</div>
                            <div class="info-value">
                                <?php if($antecedentes['pdf_sarlaft'] ?? ''){ ?>
                                    <span class="badge bg-success">✅ Firmado</span>
                                    <button onclick="verPDF('<?= $antecedentes['pdf_sarlaft'] ?>','antecedentes')" class="btn btn-sm btn-primary ms-2">📄 Ver</button>
                                <?php } else { ?>
                                    <span class="badge bg-danger">❌ Pendiente</span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Fecha firma SARLAFT</div>
                            <div class="info-value"><?= $antecedentes['fecha_sarlaft'] ?? 'Sin registro' ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">Vence</div>
                            <div class="info-value">
                                <?php if($antecedentes['fecha_vencimiento_sarlaft'] ?? ''){ ?>
                                    <?php
                                    $diasVence = (strtotime($antecedentes['fecha_vencimiento_sarlaft']) - strtotime(date('Y-m-d'))) / 86400;
                                    if($diasVence < 0){ echo '<span class="badge bg-danger">VENCIDO</span>'; }
                                    elseif($diasVence <= 30){ echo '<span class="badge bg-warning">POR VENCER</span>'; }
                                    else { echo '<span class="badge bg-success">VIGENTE</span>'; }
                                    ?>
                                    — <?= $antecedentes['fecha_vencimiento_sarlaft'] ?>
                                <?php } else { ?>
                                    Sin registro
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $docs = [
                    'pdf_policia'      => '🚔 Policía Nacional',
                    'pdf_procuraduria' => '⚖️ Procuraduría',
                    'pdf_simit'        => '🚗 SIMIT',
                    'pdf_contraloria'  => '🏛️ Contraloría',
                    'pdf_runt'         => '🚛 RUNT',
                    'pdf_lista_clinton'=> '🌐 Lista Clinton',
                    'pdf_judicatura'   => '👨‍⚖️ Judicatura',
                    'pdf_actualizacion'=> '🔄 Actualización Antecedentes'
                ];
                ?>
                <div class="row g-3">
                <?php foreach($docs as $campo => $nombre){ ?>
                    <div class="col-md-6">
                        <div class="doc-item">
                            <div>
                                <div class="doc-item-info"><?= $nombre ?></div>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <?php if($antecedentes[$campo] ?? ''){ ?>
                                    <span class="badge bg-success">✅</span>
                                    <button onclick="verPDF('<?= $antecedentes[$campo] ?>','antecedentes')" class="btn btn-sm btn-primary">📄 Ver</button>
                                <?php } else { ?>
                                    <span class="badge bg-danger">❌ Pendiente</span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </div>

            </div>
        </div>
    </div>

</div><!-- /main-content -->

<!-- html2canvas para descarga como imagen -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

</body>
</html>