<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$id = $_GET['id'];

if(isset($_POST['guardar'])){

    $curso_id = $_POST['curso_id'];
    $fecha_realizacion = $_POST['fecha_realizacion'];

    $fecha_vencimiento = date('Y-m-d', strtotime($fecha_realizacion . ' +1 year'));

    $pdf_path = "";
    if(isset($_FILES['pdf']) && $_FILES['pdf']['size'] > 0){
        $nombre_pdf = time() . '_' . $_FILES['pdf']['name'];
        $destino = "../../uploads/cursos/" . $nombre_pdf;
        if(!is_dir("../../uploads/cursos/")){
            mkdir("../../uploads/cursos/", 0777, true);
        }
        move_uploaded_file($_FILES['pdf']['tmp_name'], $destino);
        $pdf_path = $nombre_pdf;
    }

    $query = "INSERT INTO empleado_cursos(
        empleado_id,
        curso_id,
        fecha_realizacion,
        fecha_vencimiento,
        pdf_soporte
    ) VALUES (
        '$id',
        '$curso_id',
        '$fecha_realizacion',
        '$fecha_vencimiento',
        '$pdf_path'
    )";

    mysqli_query($conn,$query);

    header("Location: perfil.php?id=$id");
    exit();
}

$cursos = mysqli_query($conn,"SELECT * FROM cursos WHERE activo=1 ORDER BY nombre ASC");

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agregar Curso | VIGIA MASATRANS</title>

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
      <h1>📚 Agregar Curso</h1>
      <p>Registra el curso y su fecha de vencimiento se calculará automáticamente.</p>
    </div>
    <div class="page-header-actions">
      <a href="perfil.php?id=<?= $id ?>" class="btn btn-outline">← Volver al perfil</a>
    </div>
  </section>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">📋 Datos del curso</div>
    </div>
    <div class="panel-body">

      <form method="POST" enctype="multipart/form-data">

        <div class="row">

          <div class="col-md-6 mb-4">
            <label class="form-label">Curso <span class="req">*</span></label>
            <select name="curso_id" class="form-control" required>
              <option value="">Seleccionar curso...</option>
              <?php while($curso = mysqli_fetch_assoc($cursos)){ ?>
                <option value="<?= $curso['id'] ?>">
                  <?= $curso['nombre'] ?> — vigencia 1 año
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="col-md-6 mb-4">
            <label class="form-label">Fecha de realización <span class="req">*</span></label>
            <input type="date" name="fecha_realizacion" class="form-control" required>
          </div>

          <div class="col-md-12 mb-4">
            <label class="form-label">Soporte PDF (certificado de la IPS)</label>
            <div class="upload-area" onclick="document.getElementById('inputPDF').click()">
              <div id="textoUpload">
                📄 Haz clic aquí para subir el PDF del certificado
              </div>
              <input type="file" id="inputPDF" name="pdf" accept=".pdf" style="display:none"
              onchange="document.getElementById('textoUpload').innerHTML = '✅ Archivo seleccionado: ' + this.files[0].name">
            </div>
            <small class="text-muted mt-2 d-block">Sube el PDF que envió la IPS como soporte del curso. Máx. 10MB.</small>
          </div>

        </div>

        <div class="alert-msg warning mb-4">
          ⚠️ La fecha de vencimiento se calculará automáticamente: <strong>1 año</strong> después de la fecha de realización.
        </div>

        <div class="d-flex gap-3">
          <button type="submit" name="guardar" class="btn btn-primary btn-lg">
            💾 Guardar Curso
          </button>
          <a href="perfil.php?id=<?= $id ?>" class="btn btn-outline btn-lg">
            Cancelar
          </a>
        </div>

      </form>

    </div>
  </div>

</div>

</body>
</html>