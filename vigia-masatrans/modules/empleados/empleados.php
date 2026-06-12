<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$busqueda = "";
if(isset($_GET['buscar'])){
    $busqueda = $_GET['buscar'];
}

// Queries por área
$queryTodos = "SELECT * FROM empleados WHERE (nombres LIKE '%$busqueda%' OR apellidos LIKE '%$busqueda%' OR cedula LIKE '%$busqueda%' OR cargo LIKE '%$busqueda%') ORDER BY id DESC";
$queryOperativos = "SELECT * FROM empleados WHERE area='OPERATIVO' AND (nombres LIKE '%$busqueda%' OR apellidos LIKE '%$busqueda%' OR cedula LIKE '%$busqueda%' OR cargo LIKE '%$busqueda%') ORDER BY id DESC";
$queryAdministrativos = "SELECT * FROM empleados WHERE area='ADMINISTRATIVO' AND (nombres LIKE '%$busqueda%' OR apellidos LIKE '%$busqueda%' OR cedula LIKE '%$busqueda%' OR cargo LIKE '%$busqueda%') ORDER BY id DESC";

$rTodos = mysqli_query($conn, $queryTodos);
$rOperativos = mysqli_query($conn, $queryOperativos);
$rAdministrativos = mysqli_query($conn, $queryAdministrativos);

$totalTodos = mysqli_num_rows($rTodos);
$totalOperativos = mysqli_num_rows($rOperativos);
$totalAdministrativos = mysqli_num_rows($rAdministrativos);

// Cargar todas las inducciones indexadas por empleado_id
$inducciones = [];
$rInd = mysqli_query($conn, "SELECT * FROM inducciones");
while($ind = mysqli_fetch_assoc($rInd)){
    $inducciones[$ind['empleado_id']] = $ind;
}

// Función estado de inducción
function estadoInduccion($ind){
    if(!$ind) return ['label' => 'Sin inducción', 'class' => 'badge-sin', 'icon' => '⚪'];
    $hoy = strtotime(date('Y-m-d'));
    $venc = strtotime($ind['fecha_vencimiento']);
    $diff = ($venc - $hoy) / 86400;
    if($diff < 0)       return ['label' => 'Vencida',      'class' => 'badge-vencida',  'icon' => '🔴'];
    elseif($diff <= 30) return ['label' => 'Vence pronto', 'class' => 'badge-proxima',  'icon' => '🟡'];
    else                return ['label' => 'Vigente',      'class' => 'badge-vigente',  'icon' => '🟢'];
}

// Función para el bloque del estado en el modal (info de inducción previa)
function bloqueEstadoModal($ind){
    if(!$ind){
        return '<div class="ind-estado-bloque ind-sin">
                    <div class="ind-estado-icon">📋</div>
                    <div>
                        <div class="ind-estado-titulo">Sin inducción registrada</div>
                        <div class="ind-estado-sub">Este empleado aún no tiene inducción.</div>
                    </div>
                </div>';
    }
    $hoy = strtotime(date('Y-m-d'));
    $venc = strtotime($ind['fecha_vencimiento']);
    $diff = ($venc - $hoy) / 86400;
    $vencStr = date('d/m/Y', $venc);
    $realStr = date('d/m/Y', strtotime($ind['fecha_realizacion']));

    if($diff < 0){
        $clase = 'ind-vencida'; $icono = '🔴'; $msg = 'Vencida desde ' . $vencStr;
    } elseif($diff <= 30){
        $clase = 'ind-proxima'; $icono = '🟡'; $msg = 'Vence el ' . $vencStr . ' (' . round($diff) . ' días)';
    } else {
        $clase = 'ind-vigente'; $icono = '🟢'; $msg = 'Vence el ' . $vencStr;
    }

    return '<div class="ind-estado-bloque ' . $clase . '">
                <div class="ind-estado-icon">' . $icono . '</div>
                <div>
                    <div class="ind-estado-titulo">' . $msg . '</div>
                    <div class="ind-estado-sub">Última realización: ' . $realStr . '</div>
                </div>
            </div>';
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Empleados | VIGIA MASATRANS</title>

<link rel="icon" href="../../assets/img/logo.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/style.css">

<style>
.employee-card{ border:none; border-radius:20px; }
.avatar{
    width:50px; height:50px;
    border-radius:50%;
    background:#2563eb;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    font-size:20px;
    overflow:hidden;
    flex-shrink:0;
}
.avatar img{ width:100%; height:100%; object-fit:cover; }
.search-box{ background:white; border-radius:20px; padding:20px; }
.top-actions{ display:flex; gap:10px; flex-wrap:wrap; }
.btn{ border-radius:12px; }
.table{ vertical-align:middle; }
.area-tabs{
    display:flex;
    gap:8px;
    margin-bottom:20px;
    flex-wrap:wrap;
}
.area-tab{
    background:#e2e8f0;
    color:#334155;
    border:none;
    padding:10px 24px;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    font-size:14px;
    transition: all 0.2s;
    display:flex;
    align-items:center;
    gap:8px;
}
.area-tab.active{ background:#2563eb; color:white; }
.area-tab:hover{ opacity:0.85; }
.area-tab .count{
    background:rgba(255,255,255,0.3);
    padding:2px 8px;
    border-radius:99px;
    font-size:12px;
}
.area-tab:not(.active) .count{ background:rgba(0,0,0,0.1); }

/* Badges inducción en tabla */
.badge-ind{
    display:inline-flex;
    align-items:center;
    gap:5px;
    font-size:11px;
    padding:4px 10px;
    border-radius:99px;
    font-weight:600;
    white-space:nowrap;
}
.badge-vigente  { background:#d1fae5; color:#065f46; }
.badge-proxima  { background:#fef3c7; color:#92400e; }
.badge-vencida  { background:#fee2e2; color:#991b1b; }
.badge-sin      { background:#f1f5f9; color:#64748b; }

/* MODAL INDUCCIÓN */
.modal-ind .modal-content{
    border:none;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.18);
}
.modal-ind-header{
    background:linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
    padding:24px 28px;
    display:flex;
    align-items:center;
    gap:16px;
}
.modal-ind-header-icon{
    width:48px; height:48px;
    border-radius:50%;
    background:rgba(255,255,255,0.2);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    flex-shrink:0;
}
.modal-ind-header h5{ margin:0; font-size:17px; font-weight:600; color:white; }
.modal-ind-header p{ margin:0; font-size:13px; color:rgba(255,255,255,0.75); }
.modal-ind-header .btn-close-white{
    margin-left:auto;
    background:none;
    border:none;
    color:rgba(255,255,255,0.8);
    font-size:22px;
    line-height:1;
    cursor:pointer;
    padding:0;
}
.modal-ind-body{ padding:24px 28px; }

/* Bloque estado actual */
.ind-estado-bloque{
    border-radius:12px;
    padding:14px 16px;
    display:flex;
    align-items:center;
    gap:14px;
    margin-bottom:20px;
}
.ind-estado-icon{ font-size:22px; flex-shrink:0; }
.ind-estado-titulo{ font-size:14px; font-weight:600; }
.ind-estado-sub{ font-size:12px; margin-top:2px; }
.ind-vigente  { background:#d1fae5; }
.ind-vigente .ind-estado-titulo { color:#065f46; }
.ind-vigente .ind-estado-sub    { color:#047857; }
.ind-proxima  { background:#fef3c7; }
.ind-proxima .ind-estado-titulo { color:#92400e; }
.ind-proxima .ind-estado-sub    { color:#b45309; }
.ind-vencida  { background:#fee2e2; }
.ind-vencida .ind-estado-titulo { color:#991b1b; }
.ind-vencida .ind-estado-sub    { color:#b91c1c; }
.ind-sin      { background:#f1f5f9; }
.ind-sin .ind-estado-titulo     { color:#334155; }
.ind-sin .ind-estado-sub        { color:#64748b; }

/* Campos del formulario del modal */
.ind-field{ margin-bottom:18px; }
.ind-field label{
    display:flex;
    align-items:center;
    gap:6px;
    font-size:13px;
    font-weight:600;
    color:#475569;
    margin-bottom:7px;
}
.ind-field label .lbl-icon{ font-size:15px; }
.ind-field .form-control{
    border-radius:10px;
    border:1.5px solid #e2e8f0;
    padding:10px 14px;
    font-size:14px;
    transition:border-color 0.2s;
}
.ind-field .form-control:focus{
    border-color:#0891b2;
    box-shadow:0 0 0 3px rgba(8,145,178,0.12);
}
.ind-hint{
    font-size:12px;
    color:#64748b;
    margin-top:5px;
    display:flex;
    align-items:center;
    gap:4px;
}

/* Zona de subida PDF */
.ind-upload-zone{
    border:2px dashed #cbd5e1;
    border-radius:12px;
    padding:20px;
    text-align:center;
    cursor:pointer;
    background:#f8fafc;
    transition:all 0.2s;
    position:relative;
}
.ind-upload-zone:hover{ border-color:#0891b2; background:#f0f9ff; }
.ind-upload-zone input[type=file]{
    position:absolute; top:0; left:0;
    width:100%; height:100%;
    opacity:0; cursor:pointer;
}
.ind-upload-zone .upload-icon{ font-size:28px; margin-bottom:6px; }
.ind-upload-zone p{ margin:0; font-size:13px; color:#64748b; }
.ind-upload-zone span{ color:#0891b2; font-weight:600; }
.ind-upload-zone small{ font-size:11px; color:#94a3b8; display:block; margin-top:4px; }

/* PDF ya cargado */
.ind-pdf-actual{
    margin-top:10px;
    background:#f1f5f9;
    border-radius:10px;
    padding:10px 14px;
    display:flex;
    align-items:center;
    gap:10px;
}
.ind-pdf-actual .pdf-icon{ font-size:20px; color:#dc2626; flex-shrink:0; }
.ind-pdf-actual .pdf-nombre{ font-size:13px; color:#334155; flex:1; }
.ind-pdf-actual a{ font-size:12px; color:#0891b2; text-decoration:none; font-weight:600; }

/* Footer modal */
.modal-ind-footer{
    padding:16px 28px 24px;
    border-top:1px solid #f1f5f9;
    display:flex;
    gap:10px;
    justify-content:flex-end;
}
.btn-ind-cancelar{
    padding:10px 22px;
    border-radius:10px;
    border:1.5px solid #e2e8f0;
    background:white;
    color:#64748b;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    transition:all 0.15s;
}
.btn-ind-cancelar:hover{ background:#f8fafc; }
.btn-ind-guardar{
    padding:10px 24px;
    border-radius:10px;
    border:none;
    background:#0891b2;
    color:white;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:8px;
    transition:all 0.15s;
}
.btn-ind-guardar:hover{ background:#0e7490; }
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

    <?php if(isset($_GET['induccion']) && $_GET['induccion'] == 'ok'){ ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ Inducción / Reinducción guardada correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php } ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Gestión de Empleados</h2>
            <small class="text-muted">Administración general del personal</small>
        </div>
        <div class="top-actions">
            <a href="crear.php" class="btn btn-primary">+ Nuevo Empleado</a>
            <a href="importar_excel.php" class="btn btn-success">📥 Importar Excel</a>
            <a href="exportar_excel.php" class="btn btn-success">📤 Exportar Excel</a>
            <a href="exportar_pdf.php" class="btn btn-danger">📄 Exportar PDF</a>
        </div>
    </div>

    <!-- BUSCADOR -->
    <div class="search-box shadow mb-4">
        <form method="GET">
            <div class="row">
                <div class="col-md-10">
                    <input type="text" name="buscar" class="form-control"
                    placeholder="Buscar por nombre, cédula o cargo..."
                    value="<?= $busqueda ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Buscar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- PESTAÑAS DE ÁREA -->
    <div class="area-tabs">
        <button class="area-tab active" id="btn-todos" onclick="showArea('todos')">
            👥 Todos
            <span class="count"><?= $totalTodos ?></span>
        </button>
        <button class="area-tab" id="btn-operativos" onclick="showArea('operativos')">
            🚛 Operativos
            <span class="count"><?= $totalOperativos ?></span>
        </button>
        <button class="area-tab" id="btn-administrativos" onclick="showArea('administrativos')">
            💼 Administrativos
            <span class="count"><?= $totalAdministrativos ?></span>
        </button>
    </div>

    <!-- TABLA TODOS -->
    <div id="tabla-todos">
        <div class="card employee-card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Empleado</th>
                                <th>Área</th>
                                <th>Cargo</th>
                                <th>Cédula</th>
                                <th>Celular</th>
                                <th>Estado</th>
                                <th>Inducción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($empleado = mysqli_fetch_assoc($rTodos)){
                            $ind = $inducciones[$empleado['id']] ?? null;
                            $est = estadoInduccion($ind);
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar">
                                            <?php if($empleado['foto']){ ?>
                                                <img src="../../uploads/fotos/<?= $empleado['foto'] ?>" alt="">
                                            <?php } else { ?>
                                                <?= strtoupper(substr($empleado['nombres'],0,1)) ?>
                                            <?php } ?>
                                        </div>
                                        <div>
                                            <strong><?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?></strong>
                                            <br>
                                            <small class="text-muted"><?= $empleado['correo'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($empleado['area'] == 'OPERATIVO'){ ?>
                                        <span class="badge" style="background:#6366f1;">🚛 Operativo</span>
                                    <?php } elseif($empleado['area'] == 'ADMINISTRATIVO'){ ?>
                                        <span class="badge" style="background:#0ea5e9;">💼 Administrativo</span>
                                    <?php } else { ?>
                                        <span class="badge bg-secondary">Sin asignar</span>
                                    <?php } ?>
                                </td>
                                <td><?= $empleado['cargo'] ?></td>
                                <td><?= $empleado['cedula'] ?></td>
                                <td><?= $empleado['celular'] ?></td>
                                <td>
                                    <?php if($empleado['estado'] == 'ACTIVO'){ ?>
                                        <span class="badge bg-success">ACTIVO</span>
                                    <?php } elseif($empleado['estado'] == 'INACTIVO'){ ?>
                                        <span class="badge bg-secondary">INACTIVO</span>
                                    <?php } elseif($empleado['estado'] == 'RETIRADO'){ ?>
                                        <span class="badge bg-danger">RETIRADO</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge-ind <?= $est['class'] ?>">
                                        <?= $est['icon'] ?> <?= $est['label'] ?>
                                    </span>
                                    <?php if($ind){ ?>
                                        <br><small class="text-muted" style="font-size:11px;">Vence: <?= date('d/m/Y', strtotime($ind['fecha_vencimiento'])) ?></small>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="perfil.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-primary">Perfil</a>
                                        <a href="editar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="eliminar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar empleado?')">Eliminar</a>
                                        <button type="button" class="btn btn-sm btn-info text-white"
                                            onclick="abrirInduccion(
                                                '<?= $empleado['id'] ?>',
                                                '<?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos'], ENT_QUOTES) ?>',
                                                '<?= $empleado['cargo'] ?>',
                                                '<?= $ind ? $ind['fecha_realizacion'] : '' ?>',
                                                '<?= $ind ? addslashes($ind['observaciones']) : '' ?>',
                                                '<?= $ind ? $ind['documento_pdf'] : '' ?>'
                                            )">
                                            📋 Inducción
                                        </button>
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

    <!-- TABLA OPERATIVOS -->
    <div id="tabla-operativos" style="display:none;">
        <div class="card employee-card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background:#6366f1; color:white;">
                            <tr>
                                <th>Empleado</th>
                                <th>Cargo</th>
                                <th>Cédula</th>
                                <th>Celular</th>
                                <th>Estado</th>
                                <th>Inducción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($empleado = mysqli_fetch_assoc($rOperativos)){
                            $ind = $inducciones[$empleado['id']] ?? null;
                            $est = estadoInduccion($ind);
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar">
                                            <?php if($empleado['foto']){ ?>
                                                <img src="../../uploads/fotos/<?= $empleado['foto'] ?>" alt="">
                                            <?php } else { ?>
                                                <?= strtoupper(substr($empleado['nombres'],0,1)) ?>
                                            <?php } ?>
                                        </div>
                                        <div>
                                            <strong><?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?></strong>
                                            <br>
                                            <small class="text-muted"><?= $empleado['correo'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $empleado['cargo'] ?></td>
                                <td><?= $empleado['cedula'] ?></td>
                                <td><?= $empleado['celular'] ?></td>
                                <td>
                                    <?php if($empleado['estado'] == 'ACTIVO'){ ?>
                                        <span class="badge bg-success">ACTIVO</span>
                                    <?php } elseif($empleado['estado'] == 'INACTIVO'){ ?>
                                        <span class="badge bg-secondary">INACTIVO</span>
                                    <?php } elseif($empleado['estado'] == 'RETIRADO'){ ?>
                                        <span class="badge bg-danger">RETIRADO</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge-ind <?= $est['class'] ?>">
                                        <?= $est['icon'] ?> <?= $est['label'] ?>
                                    </span>
                                    <?php if($ind){ ?>
                                        <br><small class="text-muted" style="font-size:11px;">Vence: <?= date('d/m/Y', strtotime($ind['fecha_vencimiento'])) ?></small>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="perfil.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-primary">Perfil</a>
                                        <a href="editar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="eliminar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar empleado?')">Eliminar</a>
                                        <button type="button" class="btn btn-sm btn-info text-white"
                                            onclick="abrirInduccion(
                                                '<?= $empleado['id'] ?>',
                                                '<?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos'], ENT_QUOTES) ?>',
                                                '<?= $empleado['cargo'] ?>',
                                                '<?= $ind ? $ind['fecha_realizacion'] : '' ?>',
                                                '<?= $ind ? addslashes($ind['observaciones']) : '' ?>',
                                                '<?= $ind ? $ind['documento_pdf'] : '' ?>'
                                            )">
                                            📋 Inducción
                                        </button>
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

    <!-- TABLA ADMINISTRATIVOS -->
    <div id="tabla-administrativos" style="display:none;">
        <div class="card employee-card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background:#0ea5e9; color:white;">
                            <tr>
                                <th>Empleado</th>
                                <th>Cargo</th>
                                <th>Cédula</th>
                                <th>Celular</th>
                                <th>Estado</th>
                                <th>Inducción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($empleado = mysqli_fetch_assoc($rAdministrativos)){
                            $ind = $inducciones[$empleado['id']] ?? null;
                            $est = estadoInduccion($ind);
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar">
                                            <?php if($empleado['foto']){ ?>
                                                <img src="../../uploads/fotos/<?= $empleado['foto'] ?>" alt="">
                                            <?php } else { ?>
                                                <?= strtoupper(substr($empleado['nombres'],0,1)) ?>
                                            <?php } ?>
                                        </div>
                                        <div>
                                            <strong><?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?></strong>
                                            <br>
                                            <small class="text-muted"><?= $empleado['correo'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $empleado['cargo'] ?></td>
                                <td><?= $empleado['cedula'] ?></td>
                                <td><?= $empleado['celular'] ?></td>
                                <td>
                                    <?php if($empleado['estado'] == 'ACTIVO'){ ?>
                                        <span class="badge bg-success">ACTIVO</span>
                                    <?php } elseif($empleado['estado'] == 'INACTIVO'){ ?>
                                        <span class="badge bg-secondary">INACTIVO</span>
                                    <?php } elseif($empleado['estado'] == 'RETIRADO'){ ?>
                                        <span class="badge bg-danger">RETIRADO</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge-ind <?= $est['class'] ?>">
                                        <?= $est['icon'] ?> <?= $est['label'] ?>
                                    </span>
                                    <?php if($ind){ ?>
                                        <br><small class="text-muted" style="font-size:11px;">Vence: <?= date('d/m/Y', strtotime($ind['fecha_vencimiento'])) ?></small>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="perfil.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-primary">Perfil</a>
                                        <a href="editar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="eliminar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar empleado?')">Eliminar</a>
                                        <button type="button" class="btn btn-sm btn-info text-white"
                                            onclick="abrirInduccion(
                                                '<?= $empleado['id'] ?>',
                                                '<?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos'], ENT_QUOTES) ?>',
                                                '<?= $empleado['cargo'] ?>',
                                                '<?= $ind ? $ind['fecha_realizacion'] : '' ?>',
                                                '<?= $ind ? addslashes($ind['observaciones']) : '' ?>',
                                                '<?= $ind ? $ind['documento_pdf'] : '' ?>'
                                            )">
                                            📋 Inducción
                                        </button>
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

</div><!-- /main-content -->

<!-- ===================== MODAL INDUCCIÓN ===================== -->
<div class="modal fade modal-ind" id="modalInduccion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <!-- HEADER -->
      <div class="modal-ind-header">
        <div class="modal-ind-header-icon">📋</div>
        <div>
          <h5 id="ind_titulo">Inducción / Reinducción</h5>
          <p id="ind_subtitulo">Empleado</p>
        </div>
        <button class="btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar">✕</button>
      </div>

      <form action="guardar_induccion.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="empleado_id" id="inp_empleado_id">

        <div class="modal-ind-body">

          <!-- BLOQUE ESTADO ACTUAL -->
          <div id="bloque_estado"></div>

          <!-- FECHA -->
          <div class="ind-field">
            <label>
              <span class="lbl-icon">📅</span>
              Fecha de realización <span style="color:#ef4444;">*</span>
            </label>
            <input type="date" name="fecha_realizacion" id="inp_fecha" class="form-control" required
                   onchange="actualizarVencimiento(this.value)">
            <div class="ind-hint">
              🔄 Vencimiento automático (12 meses):
              <strong id="txt_vencimiento" style="color:#0891b2; margin-left:4px;">—</strong>
            </div>
          </div>

          <!-- PDF -->
          <div class="ind-field">
            <label>
              <span class="lbl-icon">📄</span>
              Documento soporte <span style="color:#94a3b8; font-weight:400;">(PDF opcional)</span>
            </label>
            <div class="ind-upload-zone" id="upload_zone">
              <input type="file" name="documento_pdf" id="inp_pdf" accept=".pdf"
                     onchange="mostrarNombrePdf(this)">
              <div class="upload-icon">⬆️</div>
              <p>Arrastra el PDF aquí o <span>selecciona archivo</span></p>
              <small>Solo archivos .pdf</small>
            </div>
            <div id="pdf_nombre_nuevo" style="display:none; margin-top:8px;" class="ind-pdf-actual">
              <span class="pdf-icon">📕</span>
              <span class="pdf-nombre" id="txt_pdf_nuevo"></span>
              <a href="#" onclick="limpiarPdf(); return false;">✕ Quitar</a>
            </div>
            <div id="pdf_actual_bloque" style="display:none;" class="ind-pdf-actual">
              <span class="pdf-icon">📕</span>
              <span class="pdf-nombre" id="txt_pdf_actual"></span>
              <a href="#" id="lnk_pdf_actual" target="_blank">Ver PDF</a>
            </div>
          </div>

          <!-- OBSERVACIONES -->
          <div class="ind-field" style="margin-bottom:0;">
            <label>
              <span class="lbl-icon">📝</span>
              Observaciones <span style="color:#94a3b8; font-weight:400;">(opcional)</span>
            </label>
            <textarea name="observaciones" id="inp_observaciones" class="form-control"
                      rows="3" placeholder="Notas sobre la inducción..."></textarea>
          </div>

        </div><!-- /modal-ind-body -->

        <div class="modal-ind-footer">
          <button type="button" class="btn-ind-cancelar" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-ind-guardar">
            💾 Guardar inducción
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
<!-- =========================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Tabs de área
function showArea(area) {
    var areas = ['todos','operativos','administrativos'];
    for(var i = 0; i < areas.length; i++){
        document.getElementById('tabla-' + areas[i]).style.display = 'none';
        document.getElementById('btn-' + areas[i]).classList.remove('active');
    }
    document.getElementById('tabla-' + area).style.display = 'block';
    document.getElementById('btn-' + area).classList.add('active');
}

// Calcular y mostrar fecha vencimiento
function actualizarVencimiento(fechaVal){
    if(!fechaVal){ document.getElementById('txt_vencimiento').textContent = '—'; return; }
    var d = new Date(fechaVal);
    d.setFullYear(d.getFullYear() + 1);
    var dia  = String(d.getDate()).padStart(2,'0');
    var mes  = String(d.getMonth()+1).padStart(2,'0');
    var anio = d.getFullYear();
    document.getElementById('txt_vencimiento').textContent = dia + '/' + mes + '/' + anio;
}

// Mostrar nombre del archivo seleccionado
function mostrarNombrePdf(input){
    if(input.files && input.files[0]){
        document.getElementById('txt_pdf_nuevo').textContent = input.files[0].name;
        document.getElementById('pdf_nombre_nuevo').style.display = 'flex';
        document.getElementById('upload_zone').style.display = 'none';
        document.getElementById('pdf_actual_bloque').style.display = 'none';
    }
}

function limpiarPdf(){
    document.getElementById('inp_pdf').value = '';
    document.getElementById('pdf_nombre_nuevo').style.display = 'none';
    document.getElementById('upload_zone').style.display = 'block';
}

// Abrir modal con datos del empleado
function abrirInduccion(id, nombre, cargo, fecha, observaciones, pdf){
    document.getElementById('inp_empleado_id').value = id;
    document.getElementById('ind_titulo').textContent = 'Inducción / Reinducción';
    document.getElementById('ind_subtitulo').textContent = nombre + ' · ' + cargo;
    document.getElementById('inp_fecha').value = fecha;
    document.getElementById('inp_observaciones').value = observaciones;

    // Calcular vencimiento si hay fecha
    actualizarVencimiento(fecha);

    // PDF actual
    var pdfBloque = document.getElementById('pdf_actual_bloque');
    var uploadZone = document.getElementById('upload_zone');
    document.getElementById('pdf_nombre_nuevo').style.display = 'none';

    if(pdf){
        document.getElementById('txt_pdf_actual').textContent = pdf;
        document.getElementById('lnk_pdf_actual').href = '../../uploads/inducciones/' + pdf;
        pdfBloque.style.display = 'flex';
        uploadZone.style.display = 'none';
    } else {
        pdfBloque.style.display = 'none';
        uploadZone.style.display = 'block';
    }

    // Bloque estado
    var hoy = new Date();
    var bloqueHtml = '';
    if(!fecha){
        bloqueHtml = '<div class="ind-estado-bloque ind-sin"><div class="ind-estado-icon">📋</div><div><div class="ind-estado-titulo">Sin inducción registrada</div><div class="ind-estado-sub">Este empleado aún no tiene inducción.</div></div></div>';
    } else {
        var vencDate = new Date(fecha);
        vencDate.setFullYear(vencDate.getFullYear() + 1);
        var diffDias = Math.round((vencDate - hoy) / 86400000);
        var diaV = String(vencDate.getDate()).padStart(2,'0');
        var mesV = String(vencDate.getMonth()+1).padStart(2,'0');
        var anioV = vencDate.getFullYear();
        var vencStr = diaV + '/' + mesV + '/' + anioV;

        // Fecha realización formateada
        var partes = fecha.split('-');
        var realStr = partes[2] + '/' + partes[1] + '/' + partes[0];

        if(diffDias < 0){
            bloqueHtml = '<div class="ind-estado-bloque ind-vencida"><div class="ind-estado-icon">🔴</div><div><div class="ind-estado-titulo">Vencida desde ' + vencStr + '</div><div class="ind-estado-sub">Última realización: ' + realStr + '</div></div></div>';
        } else if(diffDias <= 30){
            bloqueHtml = '<div class="ind-estado-bloque ind-proxima"><div class="ind-estado-icon">🟡</div><div><div class="ind-estado-titulo">Vence el ' + vencStr + ' (' + diffDias + ' días)</div><div class="ind-estado-sub">Última realización: ' + realStr + '</div></div></div>';
        } else {
            bloqueHtml = '<div class="ind-estado-bloque ind-vigente"><div class="ind-estado-icon">🟢</div><div><div class="ind-estado-titulo">Vigente — Vence el ' + vencStr + '</div><div class="ind-estado-sub">Última realización: ' + realStr + '</div></div></div>';
        }
    }
    document.getElementById('bloque_estado').innerHTML = bloqueHtml;

    var modal = new bootstrap.Modal(document.getElementById('modalInduccion'));
    modal.show();
}
</script>

</body>
</html>