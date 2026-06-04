<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$id = $_GET['id'];

$queryEmpleado = mysqli_query($conn,"SELECT * FROM empleados WHERE id='$id'");
$empleado = mysqli_fetch_assoc($queryEmpleado);

$resultVacunas = mysqli_query($conn,"SELECT * FROM vacunas_empleado WHERE empleado_id='$id' LIMIT 1");
$vacunaExistente = mysqli_fetch_assoc($resultVacunas);

if(isset($_POST['guardar'])){

    $fv_fiebre_amarilla = $_POST['fv_fiebre_amarilla'];
    $esquema_dosis_1 = $_POST['esquema_dosis_1'];
    $esquema_dosis_2 = $_POST['esquema_dosis_2'];
    $esquema_dosis_3 = $_POST['esquema_dosis_3'];
    $esquema_dosis_4 = $_POST['esquema_dosis_4'];
    $esquema_dosis_5 = $_POST['esquema_dosis_5'];
    $covid_dosis_1 = $_POST['covid_dosis_1'];
    $covid_dosis_2 = $_POST['covid_dosis_2'];
    $covid_dosis_3 = $_POST['covid_dosis_3'];
    $covid_dosis_4 = $_POST['covid_dosis_4'];
    $observaciones = $_POST['observaciones'];

    $pdf_vacuna = $vacunaExistente['pdf_vacuna'] ?? '';

    if(isset($_FILES['pdf_vacuna']) && $_FILES['pdf_vacuna']['size'] > 0){
        if(!is_dir("../../uploads/vacunas/")){
            mkdir("../../uploads/vacunas/", 0777, true);
        }
        $nombre_pdf = time() . '_' . $id . '_vacunas.pdf';
        $destino = "../../uploads/vacunas/" . $nombre_pdf;
        move_uploaded_file($_FILES['pdf_vacuna']['tmp_name'], $destino);
        $pdf_vacuna = $nombre_pdf;
    }

    if($vacunaExistente){
        mysqli_query($conn,"UPDATE vacunas_empleado SET
            fv_fiebre_amarilla = '$fv_fiebre_amarilla',
            esquema_dosis_1 = '$esquema_dosis_1',
            esquema_dosis_2 = '$esquema_dosis_2',
            esquema_dosis_3 = '$esquema_dosis_3',
            esquema_dosis_4 = '$esquema_dosis_4',
            esquema_dosis_5 = '$esquema_dosis_5',
            covid_dosis_1 = '$covid_dosis_1',
            covid_dosis_2 = '$covid_dosis_2',
            covid_dosis_3 = '$covid_dosis_3',
            covid_dosis_4 = '$covid_dosis_4',
            observaciones = '$observaciones',
            pdf_vacuna = '$pdf_vacuna'
            WHERE empleado_id = '$id'
        ");
    } else {
        mysqli_query($conn,"INSERT INTO vacunas_empleado(
            empleado_id,
            fv_fiebre_amarilla,
            esquema_dosis_1,
            esquema_dosis_2,
            esquema_dosis_3,
            esquema_dosis_4,
            esquema_dosis_5,
            covid_dosis_1,
            covid_dosis_2,
            covid_dosis_3,
            covid_dosis_4,
            observaciones,
            pdf_vacuna
        ) VALUES (
            '$id',
            '$fv_fiebre_amarilla',
            '$esquema_dosis_1',
            '$esquema_dosis_2',
            '$esquema_dosis_3',
            '$esquema_dosis_4',
            '$esquema_dosis_5',
            '$covid_dosis_1',
            '$covid_dosis_2',
            '$covid_dosis_3',
            '$covid_dosis_4',
            '$observaciones',
            '$pdf_vacuna'
        )");
    }

    header("Location: perfil.php?id=$id");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vacunas | VIGIA MASATRANS</title>

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
      <h1>💉 Registro de Vacunas</h1>
      <p><?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?></p>
    </div>
    <div class="page-header-actions">
      <a href="perfil.php?id=<?= $id ?>" class="btn btn-outline">← Volver al perfil</a>
    </div>
  </section>

  <form method="POST" enctype="multipart/form-data">

    <!-- FIEBRE AMARILLA -->
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="panel-title">🟡 Fiebre Amarilla</div>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Fecha dosis única</label>
            <input type="date" name="fv_fiebre_amarilla" class="form-control"
            value="<?= $vacunaExistente['fv_fiebre_amarilla'] ?? '' ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- ESQUEMA DE VACUNACION -->
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="panel-title">💉 Esquema de Vacunación</div>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Dosis 1</label>
            <input type="date" name="esquema_dosis_1" class="form-control"
            value="<?= $vacunaExistente['esquema_dosis_1'] ?? '' ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Dosis 2</label>
            <input type="date" name="esquema_dosis_2" class="form-control"
            value="<?= $vacunaExistente['esquema_dosis_2'] ?? '' ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Dosis 3</label>
            <input type="date" name="esquema_dosis_3" class="form-control"
            value="<?= $vacunaExistente['esquema_dosis_3'] ?? '' ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Dosis 4</label>
            <input type="date" name="esquema_dosis_4" class="form-control"
            value="<?= $vacunaExistente['esquema_dosis_4'] ?? '' ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Dosis 5</label>
            <input type="date" name="esquema_dosis_5" class="form-control"
            value="<?= $vacunaExistente['esquema_dosis_5'] ?? '' ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- COVID -->
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="panel-title">🦠 COVID-19</div>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label">Dosis 1</label>
            <input type="date" name="covid_dosis_1" class="form-control"
            value="<?= $vacunaExistente['covid_dosis_1'] ?? '' ?>">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Dosis 2</label>
            <input type="date" name="covid_dosis_2" class="form-control"
            value="<?= $vacunaExistente['covid_dosis_2'] ?? '' ?>">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Dosis 3</label>
            <input type="date" name="covid_dosis_3" class="form-control"
            value="<?= $vacunaExistente['covid_dosis_3'] ?? '' ?>">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Dosis 4</label>
            <input type="date" name="covid_dosis_4" class="form-control"
            value="<?= $vacunaExistente['covid_dosis_4'] ?? '' ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- OBSERVACIONES Y PDF -->
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="panel-title">📋 Observaciones y Certificado</div>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="3"
            placeholder="Observaciones adicionales..."><?= $vacunaExistente['observaciones'] ?? '' ?></textarea>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Certificado PDF</label>
            <div class="upload-area" onclick="document.getElementById('inputPDF').click()">
              <div id="textoUpload">
                <?php if($vacunaExistente['pdf_vacuna'] ?? ''){ ?>
                  📄 Ya tiene PDF — haz clic para reemplazarlo
                <?php } else { ?>
                  📄 Haz clic para subir el certificado de vacunas
                <?php } ?>
              </div>
              <input type="file" id="inputPDF" name="pdf_vacuna" accept=".pdf"
              style="display:none"
              onchange="document.getElementById('textoUpload').innerHTML = '✅ ' + this.files[0].name">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex gap-3">
      <button type="submit" name="guardar" class="btn btn-primary btn-lg">
        💾 Guardar Vacunas
      </button>
      <a href="perfil.php?id=<?= $id ?>" class="btn btn-outline btn-lg">
        Cancelar
      </a>
    </div>

  </form>

</div>

</body>
</html>