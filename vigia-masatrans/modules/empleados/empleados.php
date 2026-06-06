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
.top-actions{ display:flex; gap:10px; }
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
.area-tab:not(.active) .count{
    background:rgba(0,0,0,0.1);
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Gestión de Empleados</h2>
            <small class="text-muted">Administración general del personal</small>
        </div>
        <div class="top-actions">
            <a href="crear.php" class="btn btn-primary">+ Nuevo Empleado</a>
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
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($empleado = mysqli_fetch_assoc($rTodos)){ ?>
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
                                    <div class="d-flex gap-2">
                                        <a href="perfil.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-primary">Perfil</a>
                                        <a href="editar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="eliminar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar empleado?')">Eliminar</a>
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
                        <thead style="background:#6366f1; color:black;">
                            <tr>
                                <th>Empleado</th>
                                <th>Cargo</th>
                                <th>Cédula</th>
                                <th>Celular</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($empleado = mysqli_fetch_assoc($rOperativos)){ ?>
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
                                    <div class="d-flex gap-2">
                                        <a href="perfil.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-primary">Perfil</a>
                                        <a href="editar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="eliminar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar empleado?')">Eliminar</a>
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
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($empleado = mysqli_fetch_assoc($rAdministrativos)){ ?>
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
                                    <div class="d-flex gap-2">
                                        <a href="perfil.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-primary">Perfil</a>
                                        <a href="editar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="eliminar.php?id=<?= $empleado['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar empleado?')">Eliminar</a>
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

</div>

<script>
function showArea(area) {
    var areas = ['todos','operativos','administrativos'];
    for(var i = 0; i < areas.length; i++){
        document.getElementById('tabla-' + areas[i]).style.display = 'none';
        document.getElementById('btn-' + areas[i]).classList.remove('active');
    }
    document.getElementById('tabla-' + area).style.display = 'block';
    document.getElementById('btn-' + area).classList.add('active');
}
</script>

</body>
</html>