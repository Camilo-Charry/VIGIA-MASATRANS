<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$curso_id = $_GET['curso_id'];
$empleado_id = $_GET['empleado_id'];

$result = mysqli_query($conn,"
SELECT empleado_cursos.*, cursos.nombre AS curso_nombre
FROM empleado_cursos
INNER JOIN cursos ON empleado_cursos.curso_id = cursos.id
WHERE empleado_cursos.id = '$curso_id'
");
$registro = mysqli_fetch_assoc($result);

if(isset($_POST['renovar'])){

    $fecha_realizacion = $_POST['fecha_realizacion'];
    $fecha_vencimiento = date('Y-m-d', strtotime($fecha_realizacion . ' +1 year'));

    $pdf_path = $registro['pdf_soporte'];

    if(isset($_FILES['pdf']) && $_FILES['pdf']['size'] > 0){
        // Eliminar PDF anterior
        if($pdf_path){
            $archivo_viejo = "../../uploads/cursos/" . $pdf_path;
            if(file_exists($archivo_viejo)){
                unlink($archivo_viejo);
            }
        }
        // Subir nuevo PDF
        $nombre_pdf = time() . '_' . $_FILES['pdf']['name'];
        $destino = "../../uploads/cursos/" . $nombre_pdf;
        if(!is_dir("../../uploads/cursos/")){
            mkdir("../../uploads/cursos/", 0777, true);
        }
        move_uploaded_file($_FILES['pdf']['tmp_name'], $destino);
        $pdf_path = $nombre_pdf;
    }

    mysqli_query($conn,"
    UPDATE empleado_cursos SET
    fecha_realizacion = '$fecha_realizacion',
    fecha_vencimiento = '$fecha_vencimiento',
    pdf_soporte = '$pdf_path'
    WHERE id = '$curso_id'
    ");

    header("Location: perfil.php?id=$empleado_id");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Renovar Curso | VIGIA MASATRANS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/style.css">

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

  <section class="page-header">
    <div class="page-header-left">
      <h1>🔄 Renovar Curso</h1>
      <p>Actualiza las fechas y el certificado del curso.</p>
    </div>
    <div class="page-header-actions">
      <a href="perfil.php?id=<?= $empleado_id ?>" class="btn btn-outline">← Volver al perfil</a>
    </div>
  </section>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">📋 <?= $registro['curso_nombre'] ?></div>
    </div>
    <div class="panel-body">

      <div class="alert-msg warning mb-4">
        ⚠️ Fecha de vencimiento actual: <strong><?= $registro['fecha_vencimiento'] ?></strong>
      </div>

      <form method="POST" enctype="multipart/form-data">

        <div class="row">

          <div class="col-md-6 mb-4">
            <label class="form-label">Nueva fecha de realización <span class="req">*</span></label>
            <input type="date" name="fecha_realizacion" class="form-control" required
            value="<?= $registro['fecha_realizacion'] ?>">
          </div>

          <div class="col-md-6 mb-4">
            <label class="form-label">Nuevo soporte PDF</label>
            <div class="upload-area" onclick="document.getElementById('inputPDF').click()">
              <div id="textoUpload">
                <?php if($registro['pdf_soporte']){ ?>
                  📄 Ya tiene PDF — haz clic para reemplazarlo
                <?php } else { ?>
                  📄 Haz clic para subir el nuevo PDF
                <?php } ?>
              </div>
              <input type="file" id="inputPDF" name="pdf" accept=".pdf" style="display:none"
              onchange="document.getElementById('textoUpload').innerHTML = '✅ ' + this.files[0].name">
            </div>
          </div>

        </div>

        <div class="alert-msg success mb-4">
          ✅ La nueva fecha de vencimiento se calculará automáticamente: <strong>1 año</strong> después de la fecha de realización.
        </div>

        <div class="d-flex gap-3">
          <button type="submit" name="renovar" class="btn btn-primary btn-lg">
            🔄 Renovar Curso
          </button>
          <a href="perfil.php?id=<?= $empleado_id ?>" class="btn btn-outline btn-lg">
            Cancelar
          </a>
        </div>

      </form>

    </div>
  </div>

</div>

</body>
</html>