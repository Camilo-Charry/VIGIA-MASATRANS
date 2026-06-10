<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$mensaje = "";
$errores = [];
$importados = 0;

if(isset($_POST['importar']) && isset($_FILES['excel'])){

    $area = $_POST['area'];
    $archivo = $_FILES['excel']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['excel']['name'], PATHINFO_EXTENSION));

    if(!in_array($extension, ['xlsx','xls'])){
        $mensaje = "error";
        $errores[] = "Solo se permiten archivos Excel (.xlsx o .xls)";
    } else {

        require '../../vendor/autoload.php';

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);

            for($i = 2; $i < count($rows); $i++){
                $row = $rows[$i];

                if(empty(trim($row[1] ?? '')) && empty(trim($row[2] ?? ''))){
                    continue;
                }

                $esc = function($val) use ($conn) {
                    return mysqli_real_escape_string($conn, trim($val ?? ''));
                };

                $fechaExcel = function($val) {
                    if(empty($val) || $val == 'N/A') return '';
                    if($val instanceof \DateTime) return $val->format('Y-m-d');
                    if(is_numeric($val)) return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
                    if(is_string($val) && strtotime($val)) return date('Y-m-d', strtotime($val));
                    return '';
                };

                // DATOS PERSONALES
                $nombres               = $esc($row[1]);
                $apellidos             = $esc($row[2]);
                $cargo                 = $esc($row[3]);
                $rol_conductor         = strtoupper(trim($row[4] ?? '')) == 'SI' ? 'SI' : 'NO';
                $cedula                = $esc($row[5]);
                $lugar_expedicion      = $esc($row[6]);
                $fecha_exp_cedula      = $fechaExcel($row[7]);
                $rh                    = $esc($row[8]);
                $nivel_academico       = $esc($row[9]);
                $fecha_nacimiento      = $fechaExcel($row[10]);
                $ciudad_nacimiento     = $esc($row[11]);
                $departamento_nac      = $esc($row[12]);
                $genero                = $esc($row[13]);
                $vehiculo_desplaz      = $esc($row[14]);

                // LICENCIA MOTO
                $cat_moto              = $esc($row[15]);
                $venc_moto             = $fechaExcel($row[16]);
                $restricciones_moto    = $esc($row[17]);

                // LICENCIA CARRO
                $cat_carro             = $esc($row[18]);
                $venc_carro            = $fechaExcel($row[19]);
                $restricciones_carro   = $esc($row[20]);

                // LABORAL
                $contrato              = $esc($row[21]);
                $tipo_contrato         = $esc($row[22]);
                $fecha_ingreso         = $fechaExcel($row[23]);
                $reinduccion           = $fechaExcel($row[24]);
                $base_operacion        = $esc($row[25]);
                $turno_trabajo         = $esc($row[26]);
                $siplaft               = $fechaExcel($row[27]) ? 'SI' : 'NO';
                $verificacion_ant      = $fechaExcel($row[28]) ? 'SI' : 'NO';

                // VEHICULOS
                $cant_moto             = $esc($row[29]);
                $tipo_moto             = $esc($row[30]);
                $estado_moto           = $esc($row[31]);
                $cant_carro            = $esc($row[32]);
                $tipo_carro            = $esc($row[33]);
                $estado_carro          = $esc($row[34]);

                // RESIDENCIA
                $direccion             = $esc($row[57]);
                $departamento_res      = $esc($row[58]);
                $ciudad_res            = $esc($row[59]);
                $tipo_vivienda         = $esc($row[60]);
                $celular               = $esc($row[61]);
                $correo                = $esc($row[62]);
                $estado_civil          = $esc($row[63]);
                $raza                  = $esc($row[64]);
                $composicion_familiar  = $esc($row[65]);
                $estrato               = $esc($row[66]);

                // EMERGENCIA
                $contacto_emergencia   = $esc($row[67]);
                $parentesco_emergencia = $esc($row[68]);
                $celular_emergencia    = $esc($row[69]);

                // SEGURIDAD SOCIAL
                $eps                   = $esc($row[70]);
                $pension               = $esc($row[71]);
                $cesantias             = $esc($row[72]);
                $arl                   = $esc($row[73]);
                $nivel_riesgo          = $esc($row[74]);
                $caja_compensacion     = $esc($row[75]);
                $salario_base          = is_numeric(trim($row[89] ?? '')) ? trim($row[89]) : 0;

                // Validar cédula
                if(empty($cedula)){
                    $errores[] = "Fila " . ($i+1) . ": Sin cédula — omitida";
                    continue;
                }

                // Verificar duplicado
                $existe = mysqli_query($conn,"SELECT id FROM empleados WHERE cedula='$cedula'");
                if(mysqli_num_rows($existe) > 0){
                    $errores[] = "Fila " . ($i+1) . ": Cédula $cedula ($nombres $apellidos) ya existe — omitida";
                    continue;
                }

                // INSERTAR EMPLEADO
                $query = "INSERT INTO empleados(
                    nombres, apellidos, cedula, lugar_expedicion_cc,
                    fecha_exp_cedula, rh, fecha_nacimiento,
                    ciudad_nacimiento, departamento_nacimiento,
                    genero, nivel_academico, vehiculo_desplazamiento,
                    cargo, rol_conductor,
                    fecha_ingreso, reinduccion,
                    contrato, tipo_contrato,
                    turno_trabajo, base_operacion,
                    siplaft, verificacion_antecedentes,
                    cantidad_motocicleta, tipo_codigo_motocicleta, estado_pago_motocicleta,
                    cantidad_carro, tipo_codigo_carro, estado_pago_carro,
                    direccion, departamento_residencia, ciudad_residencia,
                    tipo_vivienda, celular, correo,
                    estado_civil, raza, composicion_familiar, estrato,
                    contacto_emergencia, parentesco_emergencia, celular_emergencia,
                    eps, pension, cesantias, arl,
                    nivel_riesgo, caja_compensacion, salario_base,
                    area, estado
                ) VALUES (
                    '$nombres', '$apellidos', '$cedula', '$lugar_expedicion',
                    '$fecha_exp_cedula', '$rh', '$fecha_nacimiento',
                    '$ciudad_nacimiento', '$departamento_nac',
                    '$genero', '$nivel_academico', '$vehiculo_desplaz',
                    '$cargo', '$rol_conductor',
                    '$fecha_ingreso', '$reinduccion',
                    '$contrato', '$tipo_contrato',
                    '$turno_trabajo', '$base_operacion',
                    '$siplaft', '$verificacion_ant',
                    '$cant_moto', '$tipo_moto', '$estado_moto',
                    '$cant_carro', '$tipo_carro', '$estado_carro',
                    '$direccion', '$departamento_res', '$ciudad_res',
                    '$tipo_vivienda', '$celular', '$correo',
                    '$estado_civil', '$raza', '$composicion_familiar', '$estrato',
                    '$contacto_emergencia', '$parentesco_emergencia', '$celular_emergencia',
                    '$eps', '$pension', '$cesantias', '$arl',
                    '$nivel_riesgo', '$caja_compensacion', '$salario_base',
                    '$area', 'ACTIVO'
                )";

                if(mysqli_query($conn, $query)){
                    $empleado_id = mysqli_insert_id($conn);
                    $importados++;

                    // LICENCIA CARRO
                    if(!empty($cat_carro) && $cat_carro != 'N/A'){
                        mysqli_query($conn,"INSERT INTO licencias(
                            empleado_id, categoria, fecha_vencimiento, restricciones
                        ) VALUES (
                            '$empleado_id', '$cat_carro', '$venc_carro', '$restricciones_carro'
                        )");
                    }

                    // CURSOS
                    $cursos_excel = [
                        40 => 'PRIMEROS AUXILIOS',
                        41 => 'MECANICA BASICA',
                        42 => 'RIESGO EN LAS OPERACIONES DE IZAJE DE CARGA',
                        44 => 'MANEJO DEFENSIVO',
                        45 => 'MANEJO DE EXTINTORES',
                        46 => 'EXTINCION DE FUEGO',
                        47 => 'TRABAJO EN ALTURAS',
                        48 => 'MANEJO DE SUSTANCIAS PELIGROSAS',
                    ];

                    foreach($cursos_excel as $col => $nombre_curso){
                        $fecha_venc_curso = $fechaExcel($row[$col]);
                        if(!empty($fecha_venc_curso)){
                            $qCurso = mysqli_query($conn,"SELECT id FROM cursos WHERE nombre LIKE '%" . mysqli_real_escape_string($conn, $nombre_curso) . "%' LIMIT 1");
                            if($qCurso && mysqli_num_rows($qCurso) > 0){
                                $curso = mysqli_fetch_assoc($qCurso);
                                $fecha_real_curso = date('Y-m-d', strtotime($fecha_venc_curso . ' -1 year'));
                                mysqli_query($conn,"INSERT INTO empleado_cursos(
                                    empleado_id, curso_id, fecha_realizacion, fecha_vencimiento
                                ) VALUES (
                                    '$empleado_id', '{$curso['id']}', '$fecha_real_curso', '$fecha_venc_curso'
                                )");
                            }
                        }
                    }

                    // VACUNAS
                    $fv_fiebre_amarilla = $fechaExcel($row[76]);
                    $esquema_dosis_1    = $fechaExcel($row[77]);
                    $esquema_dosis_2    = $fechaExcel($row[78]);
                    $esquema_dosis_3    = $fechaExcel($row[79]);
                    $esquema_dosis_4    = $fechaExcel($row[80]);
                    $esquema_dosis_5    = $fechaExcel($row[81]);
                    $covid_dosis_1      = $fechaExcel($row[82]);
                    $covid_dosis_2      = $fechaExcel($row[83]);
                    $covid_dosis_3      = $fechaExcel($row[84]);
                    $covid_dosis_4      = $fechaExcel($row[85]);

                    if($fv_fiebre_amarilla || $esquema_dosis_1 || $covid_dosis_1){
                        mysqli_query($conn,"INSERT INTO vacunas_empleado(
                            empleado_id,
                            fv_fiebre_amarilla,
                            esquema_dosis_1, esquema_dosis_2, esquema_dosis_3,
                            esquema_dosis_4, esquema_dosis_5,
                            covid_dosis_1, covid_dosis_2, covid_dosis_3, covid_dosis_4
                        ) VALUES (
                            '$empleado_id',
                            '$fv_fiebre_amarilla',
                            '$esquema_dosis_1', '$esquema_dosis_2', '$esquema_dosis_3',
                            '$esquema_dosis_4', '$esquema_dosis_5',
                            '$covid_dosis_1', '$covid_dosis_2', '$covid_dosis_3', '$covid_dosis_4'
                        )");
                    }

                } else {
                    $errores[] = "Fila " . ($i+1) . ": Error — $nombres $apellidos — " . mysqli_error($conn);
                }
            }

            $mensaje = "ok";

        } catch(Exception $e){
            $mensaje = "error";
            $errores[] = "Error: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Importar Excel | VIGIA MASATRANS</title>

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
      <h1>📥 Importar Empleados desde Excel</h1>
      <p>Carga masiva completa — empleados, cursos, licencias y vacunas.</p>
    </div>
    <div class="page-header-actions">
      <a href="empleados.php" class="btn btn-outline">← Volver</a>
    </div>
  </section>

  <?php if($mensaje == 'ok'){ ?>
    <div class="alert-msg success mb-4">
      ✅ Importación completada — <strong><?= $importados ?> empleados</strong> importados con todos sus datos.
      <?php if(count($errores) > 0){ echo " Con " . count($errores) . " advertencias."; } ?>
    </div>
  <?php } elseif($mensaje == 'error'){ ?>
    <div class="alert-msg error mb-4">❌ Error en la importación.</div>
  <?php } ?>

  <?php if(count($errores) > 0){ ?>
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="panel-title">⚠️ Advertencias (<?= count($errores) ?>)</div>
      </div>
      <div class="panel-body">
        <?php foreach($errores as $e){ ?>
          <div style="padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; color:#64748b;">
            — <?= $e ?>
          </div>
        <?php } ?>
      </div>
    </div>
  <?php } ?>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">📋 Cargar archivo Excel</div>
    </div>
    <div class="panel-body">

      <div class="alert-msg warning mb-4">
        ⚠️ Se importarán <strong>todos los datos</strong> del Excel incluyendo cursos, licencias y vacunas. Los empleados con cédula duplicada serán omitidos automáticamente.
      </div>

      <form method="POST" enctype="multipart/form-data">

        <div class="row">

          <div class="col-md-4 mb-4">
            <label class="form-label">Área de los empleados <span class="req">*</span></label>
            <select name="area" class="form-control" required>
              <option value="OPERATIVO">🚛 OPERATIVO</option>
              <option value="ADMINISTRATIVO">💼 ADMINISTRATIVO</option>
            </select>
          </div>

          <div class="col-md-8 mb-4">
            <label class="form-label">Archivo Excel (.xlsx) <span class="req">*</span></label>
            <div class="upload-area" onclick="document.getElementById('inputExcel').click()">
              <div id="textoUpload">
                📊 Haz clic para seleccionar el archivo Excel
              </div>
              <input type="file" id="inputExcel" name="excel" accept=".xlsx,.xls"
              style="display:none"
              onchange="document.getElementById('textoUpload').innerHTML = '✅ ' + this.files[0].name">
            </div>
          </div>

        </div>

        <div class="d-flex gap-3">
          <button type="submit" name="importar" class="btn btn-primary btn-lg">
            📥 Importar Empleados
          </button>
          <a href="empleados.php" class="btn btn-outline btn-lg">Cancelar</a>
        </div>

      </form>

    </div>
  </div>

</div>

</body>
</html>