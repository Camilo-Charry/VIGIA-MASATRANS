<?php
// 1. Reanudamos la sesión existente
session_start();

// 2. Vaciamos todas las variables de sesión actuales
$_SESSION = array();

// 3. Destruimos la cookie de sesión en el navegador del usuario (Nivel extra de seguridad)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalmente, destruimos la sesión en el servidor
session_destroy();

// 5. Redirigimos de vuelta a la pantalla de inicio de sesión
header("Location: login.php");
exit();
?>