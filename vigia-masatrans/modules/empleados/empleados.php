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

$query = "

SELECT * FROM empleados

WHERE

nombres LIKE '%$busqueda%'
OR apellidos LIKE '%$busqueda%'
OR cedula LIKE '%$busqueda%'
OR cargo LIKE '%$busqueda%'

ORDER BY id DESC

";

$resultado = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Empleados | VIGIA MASATRANS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="../../assets/css/style.css">

<style>

.employee-card{
    border:none;
    border-radius:20px;
}

.avatar{
    width:50px;
    height:50px;
    border-radius:50%;
    background:#2563eb;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    font-size:20px;
}

.search-box{
    background:white;
    border-radius:20px;
    padding:20px;
}

.top-actions{
    display:flex;
    gap:10px;
}

.btn{
    border-radius:12px;
}

.table{
    vertical-align:middle;
}

</style>

</head>
<body>

<!-- SIDEBAR -->

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

    <a href="../../logout.php">
        🚪 Cerrar sesión
    </a>

</div>

<!-- MAIN -->

<div class="main-content">

    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold">
                Gestión de Empleados
            </h2>

            <small class="text-muted">
                Administración general del personal
            </small>

        </div>

        <div class="top-actions">

            <a href="crear.php"
            class="btn btn-primary">

                + Nuevo Empleado

            </a>

            <a href="exportar_excel.php"
            class="btn btn-success">

                📤 Exportar Excel

            </a>

            <a href="exportar_pdf.php"
            class="btn btn-danger">

                📄 Exportar PDF

            </a>

        </div>

    </div>

    <!-- BUSCADOR -->

    <div class="search-box shadow mb-4">

        <form method="GET">

            <div class="row">

                <div class="col-md-10">

                    <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Buscar por nombre, cédula o cargo..."
                    value="<?= $busqueda ?>">

                </div>

                <div class="col-md-2">

                    <button
                    class="btn btn-primary w-100">

                        Buscar

                    </button>

                </div>

            </div>

        </form>

    </div>

    <!-- TABLA -->

    <div class="card employee-card shadow">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>Empleado</th>
                            <th>Cargo</th>
                            <th>Cédula</th>
                            <th>Celular</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Acciones</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while($empleado = mysqli_fetch_assoc($resultado)){ ?>

                        <tr>

                            <td>

                                <div class="d-flex align-items-center gap-3">

                                    <div class="avatar">

                                        <?= strtoupper(substr($empleado['nombres'],0,1)) ?>

                                    </div>

                                    <div>

                                        <strong>

                                            <?= $empleado['nombres'] ?>
                                            <?= $empleado['apellidos'] ?>

                                        </strong>

                                        <br>

                                        <small class="text-muted">

                                            <?= $empleado['correo'] ?>

                                        </small>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <?= $empleado['cargo'] ?>

                            </td>

                            <td>

                                <?= $empleado['cedula'] ?>

                            </td>

                            <td>

                                <?= $empleado['celular'] ?>

                            </td>

                            <td>

                                <?= $empleado['correo'] ?>

                            </td>

                            <td>

                                <span class="badge bg-success">

                                    ACTIVO

                                </span>

                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    <a href="perfil.php?id=<?= $empleado['id'] ?>"
                                    class="btn btn-sm btn-primary">

                                        Perfil

                                    </a>

                                    <a href="editar.php?id=<?= $empleado['id'] ?>"
                                    class="btn btn-sm btn-warning">

                                        Editar

                                    </a>

                                    <a href="eliminar.php?id=<?= $empleado['id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Eliminar empleado?')">

                                        Eliminar

                                    </a>

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

</body>
</html>