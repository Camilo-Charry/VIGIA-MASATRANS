<?php

session_start();

include("config/database.php");

$error = "";

if(isset($_POST['login'])){

    $usuario = $_POST['usuario'];
    $password = md5($_POST['password']);

    $query = "

    SELECT * FROM usuarios

    WHERE usuario='$usuario'
    AND password='$password'
    AND estado='ACTIVO'

    ";

    $resultado = mysqli_query($conn,$query);

    if(mysqli_num_rows($resultado) > 0){

        $datos = mysqli_fetch_assoc($resultado);

        $_SESSION['id'] = $datos['id'];
        $_SESSION['nombre'] = $datos['nombre'];

        header("Location: dashboard.php");

    }else{

        $error = "Usuario o contraseña incorrectos";

    }

}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | VIGIA MASATRANS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#0f172a;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family:Arial;
}

.login-box{
    background:white;
    width:400px;
    padding:40px;
    border-radius:20px;
}

.logo{
    text-align:center;
    font-size:28px;
    font-weight:bold;
    color:#2563eb;
    margin-bottom:30px;
}

</style>

</head>
<body>

<div class="login-box shadow">

    <div class="logo">
        VIGIA MASATRANS
    </div>

    <?php if($error != ""){ ?>

        <div class="alert alert-danger">

            <?= $error ?>

        </div>

    <?php } ?>

    <form method="POST">

        <div class="mb-3">

            <label>
                Usuario
            </label>

            <input
            type="text"
            name="usuario"
            class="form-control"
            required>

        </div>

        <div class="mb-4">

            <label>
                Contraseña
            </label>

            <input
            type="password"
            name="password"
            class="form-control"
            required>

        </div>

        <button
        type="submit"
        name="login"
        class="btn btn-primary w-100">

            Iniciar Sesión

        </button>

    </form>

</div>

</body>
</html>