<?php
session_start();
include("config/database.php");
$error = "";
if(isset($_POST['login'])){
    $usuario = mysqli_real_escape_string($conn, $_POST['usuario']);
    $password = md5($_POST['password']);
    $query = "SELECT * FROM usuarios WHERE usuario='$usuario' AND password='$password' AND estado='ACTIVO'";
    $resultado = mysqli_query($conn,$query);
    if(mysqli_num_rows($resultado) > 0){
        $datos = mysqli_fetch_assoc($resultado);
        $_SESSION['id']     = $datos['id'];
        $_SESSION['nombre'] = $datos['nombre'];
        $_SESSION['rol']    = $datos['rol'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar Sesión | VIGIA MASATRANS</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
  .login-page { background: #0a1628; }
</style>
</head>
<body>
<div class="login-page">

  <div class="login-box">

    <div class="login-brand">
      <div class="brand-icon">🚛</div>
      <h1>VIGIA MASATRANS</h1>
      <p>Sistema de Gestión HSEQ &amp; Talento Humano</p>
    </div>

    <?php if($error): ?>
    <div class="alert-msg error">
      ⚠️ <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">

      <div class="field">
        <label class="form-label">Usuario</label>
        <input type="text" name="usuario" class="form-control"
               placeholder="Ingrese su usuario" required autofocus>
      </div>

      <div class="field">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control"
               placeholder="••••••••" required>
      </div>

      <button type="submit" name="login" class="btn btn-primary w-100 btn-lg mt-3">
        Iniciar Sesión →
      </button>

    </form>

    <p style="text-align:center; margin-top:24px; font-size:12px; color:#94a3b8;">
      MASATRANS S.A.S &nbsp;·&nbsp; Sistema protegido de acceso restringido
    </p>

  </div>

</div>
</body>
</html>