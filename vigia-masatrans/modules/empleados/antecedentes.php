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

$queryAnt = mysqli_query($conn,"SELECT * FROM antecedentes_empleado WHERE empleado_id='$id' LIMIT 1");
$antecedentes = mysqli_fetch_assoc($queryAnt);

if(isset($_POST['guardar'])){

    $fecha_sarlaft = $_POST['fecha_sarlaft'];
    $fecha_consulta = $_POST['fecha_consulta'];
    $observaciones = $_POST['observaciones'];
    $fecha_vencimiento_sarlaft = date('Y-m-d', strtotime($fecha_sarlaft . ' +1 year'));

    $documentos = [
        'pdf_sarlaft',
        'pdf_policia',
        'pdf_procuraduria',
        'pdf_simit',
        'pdf_contraloria',
        'pdf_runt',
        'pdf_lista_clinton',
        'pdf_judicatura',
        'pdf_actualizacion'
    ];

    $archivos = [];
    foreach($documentos as $doc){
        $archivos[$doc] = $antecedentes[$doc] ?? '';
        if(isset($_FILES[$doc]) && $_FILES[$doc]['size'] > 0){
            if(!is_dir("../../uploads/antecedentes/")){
                mkdir("../../uploads/antecedentes/", 0777, true);
            }
            $nombre = time() . '_' . $id . '_' . $doc . '.pdf';
            move_uploaded_file($_FILES[$doc]['tmp_name'], "../../uploads/antecedentes/" . $nombre);
            $archivos[$doc] = $nombre;
        }
    }

    if($antecedentes){
        mysqli_query($conn,"UPDATE antecedentes_empleado SET
            pdf_sarlaft = '{$archivos['pdf_sarlaft']}',
            fecha_sarlaft = '$fecha_sarlaft',
            fecha_vencimiento_sarlaft = '$fecha_vencimiento_sarlaft',
            pdf_policia = '{$archivos['pdf_policia']}',
            pdf_procuraduria = '{$archivos['pdf_procuraduria']}',
            pdf_simit = '{$archivos['pdf_simit']}',
            pdf_contraloria = '{$archivos['pdf_contraloria']}',
            pdf_runt = '{$archivos['pdf_runt']}',
            pdf_lista_clinton = '{$archivos['pdf_lista_clinton']}',
            pdf_judicatura = '{$archivos['pdf_judicatura']}',
            pdf_actualizacion = '{$archivos['pdf_actualizacion']}',
            fecha_consulta = '$fecha_consulta',
            observaciones = '$observaciones'
            WHERE empleado_id = '$id'
        ");
    } else {
        mysqli_query($conn,"INSERT INTO antecedentes_empleado(
            empleado_id,
            pdf_sarlaft,
            fecha_sarlaft,
            fecha_vencimiento_sarlaft,
            pdf_policia,
            pdf_procuraduria,
            pdf_simit,
            pdf_contraloria,
            pdf_runt,
            pdf_lista_clinton,
            pdf_judicatura,
            pdf_actualizacion,
            fecha_consulta,
            observaciones
        ) VALUES (
            '$id',
            '{$archivos['pdf_sarlaft']}',
            '$fecha_sarlaft',
            '$fecha_vencimiento_sarlaft',
            '{$archivos['pdf_policia']}',
            '{$archivos['pdf_procuraduria']}',
            '{$archivos['pdf_simit']}',
            '{$archivos['pdf_contraloria']}',
            '{$archivos['pdf_runt']}',
            '{$archivos['pdf_lista_clinton']}',
            '{$archivos['pdf_judicatura']}',
            '{$archivos['pdf_actualizacion']}',
            '$fecha_consulta',
            '$observaciones'
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
<title>Antecedentes | VIGIA MASATRANS</title>

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
      <h1>🔍 Antecedentes y SARLAFT</h1>
      <p><?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?></p>
    </div>
    <div class="page-header-actions">
      <a href="perfil.php?id=<?= $id ?>" class="btn btn-outline">← Volver al perfil</a>
    </div>
  </section>

  <form method="POST" enctype="multipart/form-data">

    <!-- SARLAFT -->
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="panel-title">📝 SARLAFT — Autorización firmada</div>
        <?php if($antecedentes['pdf_sarlaft'] ?? ''){ ?>
          <span class="badge badge-success">✅ Tiene SARLAFT</span>
        <?php } else { ?>
          <span class="badge badge-danger">❌ Sin SARLAFT</span>
        <?php } ?>
      </div>
      <div class="panel-body">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Fecha de firma del SARLAFT</label>
            <input type="date" name="fecha_sarlaft" class="form-control"
            value="<?= $antecedentes['fecha_sarlaft'] ?? '' ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Fecha consulta antecedentes</label>
            <input type="date" name="fecha_consulta" class="form-control"
            value="<?= $antecedentes['fecha_consulta'] ?? '' ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">PDF SARLAFT firmado</label>
            <div class="upload-area" onclick="document.getElementById('input_pdf_sarlaft').click()">
              <div id="txt_pdf_sarlaft">
                <?= ($antecedentes['pdf_sarlaft'] ?? '') ? '📄 Ya tiene PDF — clic para reemplazar' : '📄 Subir SARLAFT firmado' ?>
              </div>
              <input type="file" id="input_pdf_sarlaft" name="pdf_sarlaft" accept=".pdf"
              style="display:none"
              onchange="document.getElementById('txt_pdf_sarlaft').innerHTML = '✅ ' + this.files[0].name">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ANTECEDENTES -->
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="panel-title">📋 Documentos de Antecedentes</div>
      </div>
      <div class="panel-body">
        <div class="row">

          <?php
          $docs = [
            'pdf_policia' => '🚔 Policía Nacional',
            'pdf_procuraduria' => '⚖️ Procuraduría',
            'pdf_simit' => '🚗 SIMIT',
            'pdf_contraloria' => '🏛️ Contraloría',
            'pdf_runt' => '🚛 RUNT',
            'pdf_lista_clinton' => '🌐 Lista Clinton',
            'pdf_judicatura' => '👨‍⚖️ Judicatura',
            'pdf_actualizacion' => '🔄 Actualización Antecedentes'
          ];
          foreach($docs as $campo => $nombre){ ?>

            <div class="col-md-6 mb-4">
              <label class="form-label">
                <?= $nombre ?>
                <?php if($antecedentes[$campo] ?? ''){ ?>
                  <span class="badge badge-success ms-2">✅ Subido</span>
                <?php } else { ?>
                  <span class="badge badge-danger ms-2">❌ Pendiente</span>
                <?php } ?>
              </label>
              <div class="upload-area" onclick="document.getElementById('input_<?= $campo ?>').click()">
                <div id="txt_<?= $campo ?>">
                  <?= ($antecedentes[$campo] ?? '') ? '📄 Ya tiene PDF — clic para reemplazar' : '📄 Clic para subir' ?>
                </div>
                <input type="file" id="input_<?= $campo ?>" name="<?= $campo ?>" accept=".pdf"
                style="display:none"
                onchange="document.getElementById('txt_<?= $campo ?>').innerHTML = '✅ ' + this.files[0].name">
              </div>
            </div>

          <?php } ?>

          <div class="col-md-12 mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="3"
            placeholder="Observaciones adicionales..."><?= $antecedentes['observaciones'] ?? '' ?></textarea>
          </div>

        </div>
      </div>
    </div>

    <div class="d-flex gap-3">
      <button type="submit" name="guardar" class="btn btn-primary btn-lg">
        💾 Guardar
      </button>
      <a href="perfil.php?id=<?= $id ?>" class="btn btn-outline btn-lg">
        Cancelar
      </a>
    </div>

  </form>

</div>

</body>
</html>