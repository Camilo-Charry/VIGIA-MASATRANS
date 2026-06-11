<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

$id = $_GET['id'];

$query = mysqli_query($conn,"SELECT * FROM empleados WHERE id='$id'");
$empleado = mysqli_fetch_assoc($query);

$queryLicencia = mysqli_query($conn,"SELECT * FROM licencias WHERE empleado_id='$id' LIMIT 1");
$licencia = mysqli_fetch_assoc($queryLicencia);

if(isset($_POST['actualizar'])){

    $nombres                 = $_POST['nombres'];
    $apellidos               = $_POST['apellidos'];
    $cedula                  = $_POST['cedula'];
    $lugar_expedicion_cc     = $_POST['lugar_expedicion_cc'];
    $fecha_exp_cedula        = $_POST['fecha_exp_cedula'];
    $rh                      = $_POST['rh'];
    $fecha_nacimiento        = $_POST['fecha_nacimiento'];
    $genero                  = $_POST['genero'];
    $nivel_academico         = $_POST['nivel_academico'];
    $ciudad_nacimiento       = $_POST['ciudad_nacimiento'];
    $departamento_nacimiento = $_POST['departamento_nacimiento'];
    $estado_civil            = $_POST['estado_civil'];
    $raza                    = $_POST['raza'];

    $cargo                   = $_POST['cargo'];
    $area                    = $_POST['area'];
    $rol_conductor           = $_POST['rol_conductor'];
    $fecha_ingreso           = $_POST['fecha_ingreso'];
    $contrato                = $_POST['contrato'];
    $tipo_contrato           = $_POST['tipo_contrato'];
    $turno_trabajo           = $_POST['turno_trabajo'];
    $base_operacion          = $_POST['base_operacion'];
    $siplaft                 = $_POST['siplaft'];
    $verificacion_antecedentes = $_POST['verificacion_antecedentes'];

    $direccion               = $_POST['direccion'];
    $ciudad_residencia       = $_POST['ciudad_residencia'];
    $departamento_residencia = $_POST['departamento_residencia'];
    $tipo_vivienda           = $_POST['tipo_vivienda'];
    $estrato                 = $_POST['estrato'];
    $composicion_familiar    = $_POST['composicion_familiar'];

    $celular                 = $_POST['celular'];
    $correo                  = $_POST['correo'];

    $contacto_emergencia     = $_POST['contacto_emergencia'];
    $parentesco_emergencia   = $_POST['parentesco_emergencia'];
    $celular_emergencia      = $_POST['celular_emergencia'];

    $eps                     = $_POST['eps'];
    $arl                     = $_POST['arl'];
    $pension                 = $_POST['pension'];
    $cesantias               = $_POST['cesantias'];
    $caja_compensacion       = $_POST['caja_compensacion'];
    $nivel_riesgo            = $_POST['nivel_riesgo'];
    $salario_base            = $_POST['salario_base'];

    $vehiculo_desplazamiento = $_POST['vehiculo_desplazamiento'];

    mysqli_query($conn,"
    UPDATE empleados SET
    nombres='$nombres',
    apellidos='$apellidos',
    cedula='$cedula',
    lugar_expedicion_cc='$lugar_expedicion_cc',
    fecha_exp_cedula='$fecha_exp_cedula',
    rh='$rh',
    fecha_nacimiento='$fecha_nacimiento',
    genero='$genero',
    nivel_academico='$nivel_academico',
    ciudad_nacimiento='$ciudad_nacimiento',
    departamento_nacimiento='$departamento_nacimiento',
    estado_civil='$estado_civil',
    raza='$raza',
    cargo='$cargo',
    area='$area',
    rol_conductor='$rol_conductor',
    fecha_ingreso='$fecha_ingreso',
    contrato='$contrato',
    tipo_contrato='$tipo_contrato',
    turno_trabajo='$turno_trabajo',
    base_operacion='$base_operacion',
    siplaft='$siplaft',
    verificacion_antecedentes='$verificacion_antecedentes',
    direccion='$direccion',
    ciudad_residencia='$ciudad_residencia',
    departamento_residencia='$departamento_residencia',
    tipo_vivienda='$tipo_vivienda',
    estrato='$estrato',
    composicion_familiar='$composicion_familiar',
    celular='$celular',
    correo='$correo',
    contacto_emergencia='$contacto_emergencia',
    parentesco_emergencia='$parentesco_emergencia',
    celular_emergencia='$celular_emergencia',
    eps='$eps',
    arl='$arl',
    pension='$pension',
    cesantias='$cesantias',
    caja_compensacion='$caja_compensacion',
    nivel_riesgo='$nivel_riesgo',
    salario_base='$salario_base',
    vehiculo_desplazamiento='$vehiculo_desplazamiento'
    WHERE id='$id'
    ");

    // Actualizar licencia
    $cat_licencia    = $_POST['categoria_licencia'];
    $venc_licencia   = $_POST['fecha_vencimiento_licencia'];
    $rest_licencia   = $_POST['restricciones_licencia'];

    if($licencia){
        mysqli_query($conn,"
        UPDATE licencias SET
        categoria='$cat_licencia',
        fecha_vencimiento='$venc_licencia',
        restricciones='$rest_licencia'
        WHERE empleado_id='$id'
        ");
    } else {
        mysqli_query($conn,"
        INSERT INTO licencias(empleado_id, categoria, fecha_vencimiento, restricciones)
        VALUES ('$id','$cat_licencia','$venc_licencia','$rest_licencia')
        ");
    }

    header("Location: perfil.php?id=".$id);
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Empleado | VIGIA MASATRANS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/style.css">

<style>
.form-section{
    background:white;
    border-radius:20px;
    padding:25px;
    margin-bottom:25px;
}
.section-title{
    font-weight:bold;
    margin-bottom:20px;
    color:#0f172a;
    font-size:16px;
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

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">✏️ Editar Empleado</h2>
            <small class="text-muted"><?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?></small>
        </div>
        <a href="perfil.php?id=<?= $id ?>" class="btn btn-outline">← Volver al perfil</a>
    </div>

    <form method="POST">

        <!-- INFORMACIÓN PERSONAL -->
        <div class="form-section shadow">
            <div class="section-title">👤 Información Personal</div>
            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Nombres</label>
                    <input type="text" name="nombres" class="form-control" value="<?= $empleado['nombres'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" value="<?= $empleado['apellidos'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Cédula</label>
                    <input type="text" name="cedula" class="form-control" value="<?= $empleado['cedula'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Lugar expedición CC</label>
                    <input type="text" name="lugar_expedicion_cc" class="form-control" value="<?= $empleado['lugar_expedicion_cc'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Fecha expedición CC</label>
                    <input type="date" name="fecha_exp_cedula" class="form-control" value="<?= $empleado['fecha_exp_cedula'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>RH</label>
                    <select name="rh" class="form-control">
                        <?php foreach(['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $rh){ ?>
                            <option <?= $empleado['rh'] == $rh ? 'selected' : '' ?>><?= $rh ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Fecha nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" value="<?= $empleado['fecha_nacimiento'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Género</label>
                    <select name="genero" class="form-control">
                        <?php foreach(['MASCULINO','FEMENINO','OTRO'] as $g){ ?>
                            <option <?= $empleado['genero'] == $g ? 'selected' : '' ?>><?= $g ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Nivel académico</label>
                    <select name="nivel_academico" class="form-control">
                        <?php foreach(['PRIMARIA INCOMPLETA','PRIMARIA COMPLETA','BACHILLERATO INCOMPLETO','BACHILLERATO COMPLETO','TÉCNICO','TECNÓLOGO','UNIVERSITARIO','ESPECIALIZACIÓN','MAESTRÍA','DOCTORADO'] as $n){ ?>
                            <option <?= $empleado['nivel_academico'] == $n ? 'selected' : '' ?>><?= $n ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Ciudad nacimiento</label>
                    <input type="text" name="ciudad_nacimiento" class="form-control" value="<?= $empleado['ciudad_nacimiento'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Departamento nacimiento</label>
                    <input type="text" name="departamento_nacimiento" class="form-control" value="<?= $empleado['departamento_nacimiento'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Estado civil</label>
                    <select name="estado_civil" class="form-control">
                        <?php foreach(['SOLTERO/A','CASADO/A','UNIÓN LIBRE','DIVORCIADO/A','VIUDO/A'] as $ec){ ?>
                            <option <?= $empleado['estado_civil'] == $ec ? 'selected' : '' ?>><?= $ec ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Raza</label>
                    <input type="text" name="raza" class="form-control" value="<?= $empleado['raza'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Vehículo desplazamiento</label>
                    <input type="text" name="vehiculo_desplazamiento" class="form-control" value="<?= $empleado['vehiculo_desplazamiento'] ?>">
                </div>

            </div>
        </div>

        <!-- INFORMACIÓN LABORAL -->
        <div class="form-section shadow">
            <div class="section-title">🚛 Información Laboral</div>
            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Cargo</label>
                    <input type="text" name="cargo" class="form-control" value="<?= $empleado['cargo'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Área</label>
                    <select name="area" class="form-control">
                        <option <?= $empleado['area'] == 'OPERATIVO' ? 'selected' : '' ?> value="OPERATIVO">OPERATIVO</option>
                        <option <?= $empleado['area'] == 'ADMINISTRATIVO' ? 'selected' : '' ?> value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Rol conductor</label>
                    <select name="rol_conductor" class="form-control">
                        <option <?= $empleado['rol_conductor'] == 'SI' ? 'selected' : '' ?> value="SI">SI</option>
                        <option <?= $empleado['rol_conductor'] == 'NO' ? 'selected' : '' ?> value="NO">NO</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Fecha ingreso</label>
                    <input type="date" name="fecha_ingreso" class="form-control" value="<?= $empleado['fecha_ingreso'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Contrato</label>
                    <input type="text" name="contrato" class="form-control" value="<?= $empleado['contrato'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Tipo contrato</label>
                    <select name="tipo_contrato" class="form-control">
                        <?php foreach(['TÉRMINO FIJO','TÉRMINO INDEFINIDO','OBRA O LABOR','PRESTACIÓN DE SERVICIOS','APRENDIZAJE'] as $tc){ ?>
                            <option <?= $empleado['tipo_contrato'] == $tc ? 'selected' : '' ?>><?= $tc ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Turno trabajo</label>
                    <select name="turno_trabajo" class="form-control">
                        <?php foreach(['DIURNO','NOCTURNO','MIXTO','ROTATIVO'] as $t){ ?>
                            <option <?= $empleado['turno_trabajo'] == $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Base operación</label>
                    <input type="text" name="base_operacion" class="form-control" value="<?= $empleado['base_operacion'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>SIPLAFT</label>
                    <select name="siplaft" class="form-control">
                        <option <?= $empleado['siplaft'] == 'SI' ? 'selected' : '' ?> value="SI">SI</option>
                        <option <?= $empleado['siplaft'] == 'NO' ? 'selected' : '' ?> value="NO">NO</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Verificación antecedentes</label>
                    <select name="verificacion_antecedentes" class="form-control">
                        <option <?= $empleado['verificacion_antecedentes'] == 'SI' ? 'selected' : '' ?> value="SI">SI</option>
                        <option <?= $empleado['verificacion_antecedentes'] == 'NO' ? 'selected' : '' ?> value="NO">NO</option>
                    </select>
                </div>

            </div>
        </div>

        <!-- LICENCIA -->
        <div class="form-section shadow">
            <div class="section-title">🚘 Licencia de Conducción</div>
            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Categoría</label>
                    <input type="text" name="categoria_licencia" class="form-control" value="<?= $licencia['categoria'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Fecha vencimiento</label>
                    <input type="date" name="fecha_vencimiento_licencia" class="form-control" value="<?= $licencia['fecha_vencimiento'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Restricciones</label>
                    <input type="text" name="restricciones_licencia" class="form-control" value="<?= $licencia['restricciones'] ?? '' ?>">
                </div>

            </div>
        </div>

        <!-- RESIDENCIA -->
        <div class="form-section shadow">
            <div class="section-title">📍 Residencia</div>
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Dirección</label>
                    <input type="text" name="direccion" class="form-control" value="<?= $empleado['direccion'] ?>">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad_residencia" class="form-control" value="<?= $empleado['ciudad_residencia'] ?>">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Departamento</label>
                    <input type="text" name="departamento_residencia" class="form-control" value="<?= $empleado['departamento_residencia'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Tipo vivienda</label>
                    <select name="tipo_vivienda" class="form-control">
                        <?php foreach(['PROPIA','ARRENDADA','FAMILIAR','OTRA'] as $tv){ ?>
                            <option <?= $empleado['tipo_vivienda'] == $tv ? 'selected' : '' ?>><?= $tv ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Estrato</label>
                    <input type="number" name="estrato" class="form-control" value="<?= $empleado['estrato'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Composición familiar</label>
                    <input type="text" name="composicion_familiar" class="form-control" value="<?= $empleado['composicion_familiar'] ?>">
                </div>

            </div>
        </div>

        <!-- CONTACTO -->
        <div class="form-section shadow">
            <div class="section-title">📱 Contacto</div>
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Celular</label>
                    <input type="text" name="celular" class="form-control" value="<?= $empleado['celular'] ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Correo</label>
                    <input type="email" name="correo" class="form-control" value="<?= $empleado['correo'] ?>">
                </div>

            </div>
        </div>

        <!-- EMERGENCIA -->
        <div class="form-section shadow">
            <div class="section-title">🚨 Contacto Emergencia</div>
            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Nombre contacto</label>
                    <input type="text" name="contacto_emergencia" class="form-control" value="<?= $empleado['contacto_emergencia'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Parentesco</label>
                    <input type="text" name="parentesco_emergencia" class="form-control" value="<?= $empleado['parentesco_emergencia'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Celular emergencia</label>
                    <input type="text" name="celular_emergencia" class="form-control" value="<?= $empleado['celular_emergencia'] ?>">
                </div>

            </div>
        </div>

        <!-- SEGURIDAD SOCIAL -->
        <div class="form-section shadow">
            <div class="section-title">🏥 Seguridad Social</div>
            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>EPS</label>
                    <input type="text" name="eps" class="form-control" value="<?= $empleado['eps'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>ARL</label>
                    <input type="text" name="arl" class="form-control" value="<?= $empleado['arl'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Pensión</label>
                    <input type="text" name="pension" class="form-control" value="<?= $empleado['pension'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Cesantías</label>
                    <input type="text" name="cesantias" class="form-control" value="<?= $empleado['cesantias'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Caja compensación</label>
                    <input type="text" name="caja_compensacion" class="form-control" value="<?= $empleado['caja_compensacion'] ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Nivel riesgo</label>
                    <select name="nivel_riesgo" class="form-control">
                        <?php foreach(['I','II','III','IV','V'] as $nr){ ?>
                            <option <?= $empleado['nivel_riesgo'] == $nr ? 'selected' : '' ?>><?= $nr ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Salario base</label>
                    <input type="number" name="salario_base" class="form-control" value="<?= $empleado['salario_base'] ?>">
                </div>

            </div>
        </div>

        <div class="d-flex gap-3 mb-6">
            <button type="submit" name="actualizar" class="btn btn-primary btn-lg">
                💾 Actualizar Empleado
            </button>
            <a href="perfil.php?id=<?= $id ?>" class="btn btn-outline btn-lg">
                Cancelar
            </a>
        </div>

    </form>

</div>

</body>
</html>