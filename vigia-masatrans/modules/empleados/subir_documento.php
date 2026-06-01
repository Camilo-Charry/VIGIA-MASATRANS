<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$empleado_id = $_GET['id'];

$queryEmpleado = mysqli_query($conn,"SELECT * FROM empleados WHERE id='$empleado_id'");
$empleado = mysqli_fetch_assoc($queryEmpleado);

if(isset($_POST['guardar'])){

    $tipo_documento = $_POST['nombre_documento'];
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';

    if(isset($_FILES['archivo']) && $_FILES['archivo']['size'] > 0){

        if(!is_dir("../../uploads/documentos/")){
            mkdir("../../uploads/documentos/", 0777, true);
        }

        $nombreFinal = time() . '_' . $empleado_id . '_' . $_FILES['archivo']['name'];
        $ruta = "../../uploads/documentos/" . $nombreFinal;
        move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);

        mysqli_query($conn,"INSERT INTO documentos_empleado(
            empleado_id,
            tipo_documento,
            nombre_archivo,
            descripcion
        ) VALUES (
            '$empleado_id',
            '$tipo_documento',
            '$nombreFinal',
            '$descripcion'
        )");

    }

    header("Location: perfil.php?id=" . $empleado_id);
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subir Documento | VIGIA MASATRANS</title>

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
      <h1>📁 Subir Documento</h1>
      <p><?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?></p>
    </div>
    <div class="page-header-actions">
      <a href="perfil.php?id=<?= $empleado_id ?>" class="btn btn-outline">← Volver al perfil</a>
    </div>
  </section>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">📋 Datos del documento</div>
    </div>
    <div class="panel-body">

      <form method="POST" enctype="multipart/form-data">

        <div class="row">

          <div class="col-md-6 mb-4">
            <label class="form-label">Tipo de documento <span class="req">*</span></label>
            <select name="nombre_documento" class="form-control" required>
              <option value="">Seleccionar tipo...</option>
              <option value="ARL">ARL</option>
              <option value="EPS">EPS</option>
              <option value="FONDO DE PENSIONES">Fondo de Pensiones</option>
              <option value="LICENCIA">Licencia</option>
              <option value="CONTRATO">Contrato</option>
              <option value="OTROSI">Otrosí</option>
              <option value="RENOVACION">Renovación</option>
              <option value="PREAVISO">Preaviso</option>
              <option value="RUT">RUT</option>
              <option value="HOJA DE VIDA">Hoja de Vida</option>
              <option value="CERTIFICADO RESIDENCIA">Certificado Residencia</option>
              <option value="USO DE IMAGEN">Uso de Imagen</option>
              <option value="ALCOHOL Y DROGAS">Alcohol y Drogas</option>
              <option value="CERTIFICADO ALTURAS">Certificado Alturas</option>
              <option value="CERTIFICADO OPERADOR GRUA">Certificado Operador Grúa</option>
              <option value="CERTIFICADO APAREJADOR">Certificado Aparejador</option>
              <option value="CERTIFICADO MEDICO">Certificado Médico</option>
              <option value="OTRO">Otro</option>
            </select>
          </div>

          <div class="col-md-6 mb-4">
            <label class="form-label">Descripción (opcional)</label>
            <input type="text" name="descripcion" class="form-control"
            placeholder="Ej: Contrato enero 2025...">
          </div>

          <div class="col-md-12 mb-4">
            <label class="form-label">Archivo <span class="req">*</span></label>
            <div class="upload-area" onclick="document.getElementById('inputDoc').click()">
              <div id="textoUpload">
                📄 Haz clic para seleccionar el archivo (PDF, Word, Excel, imagen)
              </div>
              <input type="file" id="inputDoc" name="archivo" required
              accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
              style="display:none"
              onchange="document.getElementById('textoUpload').innerHTML = '✅ ' + this.files[0].name">
            </div>
            <small class="text-muted mt-2 d-block">Formatos: PDF, Word, Excel, imágenes.</small>
          </div>

        </div>

        <div class="d-flex gap-3">
          <button type="submit" name="guardar" class="btn btn-primary btn-lg">
            💾 Guardar Documento
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