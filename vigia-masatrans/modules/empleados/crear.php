<?php

session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../../login.php");
    exit();
}

include("../../config/database.php");

if(isset($_POST['guardar'])){

    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $cedula = $_POST['cedula'];
    $lugar_expedicion = $_POST['lugar_expedicion'];
    $fecha_exp_cedula = $_POST['fecha_exp_cedula'];
    $rh = $_POST['rh'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $nivel_academico = $_POST['nivel_academico'];

    $cargo = $_POST['cargo'];
    $rol_conductor = $_POST['rol_conductor'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $contrato = $_POST['contrato'];
    $tipo_contrato = $_POST['tipo_contrato'];
    $turno_trabajo = $_POST['turno_trabajo'];
    $base_operacion = $_POST['base_operacion'];
    $siplaft = $_POST['siplaft'];
    $verificacion_antecedentes = $_POST['verificacion_antecedentes'];

    $categoria_licencia = $_POST['categoria_licencia'];
    $fecha_vencimiento_licencia = $_POST['fecha_vencimiento_licencia'];
    $restricciones_licencia = $_POST['restricciones_licencia'];

    $direccion = $_POST['direccion'];
    $ciudad_residencia = $_POST['ciudad_residencia'];
    $departamento_residencia = $_POST['departamento_residencia'];
    $tipo_vivienda = $_POST['tipo_vivienda'];
    $estrato = $_POST['estrato'];
    $composicion_familiar = $_POST['composicion_familiar'];

    $celular = $_POST['celular'];
    $correo = $_POST['correo'];

    $contacto_emergencia = $_POST['contacto_emergencia'];
    $parentesco_emergencia = $_POST['parentesco_emergencia'];
    $celular_emergencia = $_POST['celular_emergencia'];

    $eps = $_POST['eps'];
    $arl = $_POST['arl'];
    $pension = $_POST['pension'];
    $cesantias = $_POST['cesantias'];
    $caja_compensacion = $_POST['caja_compensacion'];
    $nivel_riesgo = $_POST['nivel_riesgo'];
    $salario_base = $_POST['salario_base'];

    $query = "INSERT INTO empleados(

        nombres,
        apellidos,
        cedula,
        lugar_expedicion,
        fecha_exp_cedula,
        rh,
        fecha_nacimiento,
        genero,
        nivel_academico,

        cargo,
        rol_conductor,
        fecha_ingreso,
        contrato,
        tipo_contrato,
        turno_trabajo,
        base_operacion,
        siplaft,
        verificacion_antecedentes,

        direccion,
        ciudad,
        departamento,
        tipo_vivienda,
        estrato,
        composicion_familiar,

        celular,
        correo,

        contacto_emergencia,
        parentesco_emergencia,
        celular_emergencia,

        eps,
        arl,
        pension,
        cesantias,
        caja_compensacion,
        nivel_riesgo,
        salario_base

    ) VALUES (

        '$nombres',
        '$apellidos',
        '$cedula',
        '$lugar_expedicion',
        '$fecha_exp_cedula',
        '$rh',
        '$fecha_nacimiento',
        '$genero',
        '$nivel_academico',

        '$cargo',
        '$rol_conductor',
        '$fecha_ingreso',
        '$contrato',
        '$tipo_contrato',
        '$turno_trabajo',
        '$base_operacion',
        '$siplaft',
        '$verificacion_antecedentes',

        '$direccion',
        '$ciudad_residencia',
        '$departamento_residencia',
        '$tipo_vivienda',
        '$estrato',
        '$composicion_familiar',

        '$celular',
        '$correo',

        '$contacto_emergencia',
        '$parentesco_emergencia',
        '$celular_emergencia',

        '$eps',
        '$arl',
        '$pension',
        '$cesantias',
        '$caja_compensacion',
        '$nivel_riesgo',
        '$salario_base'

    )";

    if(mysqli_query($conn,$query)){

        $empleado_id = mysqli_insert_id($conn);

        $queryLicencia = "INSERT INTO licencias(

            empleado_id,
            categoria,
            fecha_vencimiento,
            restricciones

        ) VALUES (

            '$empleado_id',
            '$categoria_licencia',
            '$fecha_vencimiento_licencia',
            '$restricciones_licencia'

        )";

        mysqli_query($conn,$queryLicencia);

        header("Location: empleados.php");

    }else{

        echo "Error al guardar empleado";

    }

}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nuevo Empleado | VIGIA MASATRANS</title>

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

    <div class="topbar mb-4">

        <h3>
            Nuevo Empleado
        </h3>

        <small class="text-muted">
            Registro integral del trabajador
        </small>

    </div>

    <form method="POST">

        <!-- INFORMACION PERSONAL -->

        <div class="form-section shadow">

            <div class="section-title">
                👤 Información Personal
            </div>

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Nombres</label>
                    <input type="text" name="nombres" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Cédula</label>
                    <input type="text" name="cedula" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Lugar expedición</label>
                    <input type="text" name="lugar_expedicion" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Fecha expedición</label>
                    <input type="date" name="fecha_exp_cedula" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>RH</label>

                    <select name="rh" class="form-control">

                        <option>O+</option>
                        <option>O-</option>
                        <option>A+</option>
                        <option>A-</option>
                        <option>B+</option>
                        <option>B-</option>
                        <option>AB+</option>
                        <option>AB-</option>

                    </select>

                </div>

                <div class="col-md-4 mb-3">
                    <label>Fecha nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Género</label>

                    <select name="genero" class="form-control">

                        <option>Masculino</option>
                        <option>Femenino</option>
                        <option>Otro</option>

                    </select>

                </div>

                <div class="col-md-4 mb-3">
                    <label>Nivel académico</label>
                    <input type="text" name="nivel_academico" class="form-control">
                </div>

            </div>

        </div>

        <!-- INFORMACION LABORAL -->

        <div class="form-section shadow">

            <div class="section-title">
                🚛 Información Laboral
            </div>

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Cargo</label>
                    <input type="text" name="cargo" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Rol conductor</label>

                    <select name="rol_conductor" class="form-control">

                        <option value="NO">NO</option>
                        <option value="SI">SI</option>

                    </select>

                </div>

                <div class="col-md-4 mb-3">
                    <label>Fecha ingreso</label>
                    <input type="date" name="fecha_ingreso" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Contrato</label>
                    <input type="text" name="contrato" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Tipo contrato</label>
                    <input type="text" name="tipo_contrato" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Turno trabajo</label>
                    <input type="text" name="turno_trabajo" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Base operación</label>
                    <input type="text" name="base_operacion" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>SIPLAFT</label>

                    <select name="siplaft" class="form-control">

                        <option value="NO">NO</option>
                        <option value="SI">SI</option>

                    </select>

                </div>

                <div class="col-md-4 mb-3">
                    <label>Verificación antecedentes</label>

                    <select name="verificacion_antecedentes" class="form-control">

                        <option value="NO">NO</option>
                        <option value="SI">SI</option>

                    </select>

                </div>

            </div>

        </div>

        <!-- LICENCIA -->

        <div class="form-section shadow">

            <div class="section-title">
                🚘 Licencia
            </div>

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Categoría licencia</label>
                    <input type="text" name="categoria_licencia" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Fecha vencimiento</label>
                    <input type="date" name="fecha_vencimiento_licencia" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Restricciones</label>
                    <input type="text" name="restricciones_licencia" class="form-control">
                </div>

            </div>

        </div>

        <!-- RESIDENCIA -->

        <div class="form-section shadow">

            <div class="section-title">
                📍 Residencia
            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Dirección</label>
                    <input type="text" name="direccion" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad_residencia" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Departamento</label>
                    <input type="text" name="departamento_residencia" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Tipo vivienda</label>
                    <input type="text" name="tipo_vivienda" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Estrato</label>
                    <input type="text" name="estrato" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Composición familiar</label>
                    <input type="text" name="composicion_familiar" class="form-control">
                </div>

            </div>

        </div>

        <!-- CONTACTO -->

        <div class="form-section shadow">

            <div class="section-title">
                📱 Contacto
            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Celular</label>
                    <input type="text" name="celular" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Correo</label>
                    <input type="email" name="correo" class="form-control">
                </div>

            </div>

        </div>

        <!-- EMERGENCIA -->

        <div class="form-section shadow">

            <div class="section-title">
                🚨 Contacto Emergencia
            </div>

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Nombre contacto</label>
                    <input type="text" name="contacto_emergencia" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Parentesco</label>
                    <input type="text" name="parentesco_emergencia" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Celular emergencia</label>
                    <input type="text" name="celular_emergencia" class="form-control">
                </div>

            </div>

        </div>

        <!-- SEGURIDAD SOCIAL -->

        <div class="form-section shadow">

            <div class="section-title">
                🏥 Seguridad Social
            </div>

            <div class="row">

                <div class="col-md-3 mb-3">
                    <label>EPS</label>
                    <input type="text" name="eps" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label>ARL</label>
                    <input type="text" name="arl" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Pensión</label>
                    <input type="text" name="pension" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Cesantías</label>
                    <input type="text" name="cesantias" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Caja compensación</label>
                    <input type="text" name="caja_compensacion" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Nivel riesgo</label>
                    <input type="text" name="nivel_riesgo" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Salario base</label>
                    <input type="number" name="salario_base" class="form-control">
                </div>

            </div>

        </div>

        <button type="submit"
        name="guardar"
        class="btn btn-primary btn-lg">

            Guardar Empleado

        </button>

    </form>

</div>

</body>
</html>