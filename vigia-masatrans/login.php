<?php
session_start();
if(isset($_SESSION['id'])){ header("Location: dashboard.php"); exit(); }
include("config/database.php");
$error = "";

if (isset($_POST['login'])) {
    $u = trim($_POST['usuario']); 
    $p = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, nombre, rol, password FROM usuarios WHERE usuario = ? AND estado = 'ACTIVO'");
    $stmt->bind_param("s", $u); 
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (md5($p) === $row['password']) {
            session_regenerate_id(true);
            $_SESSION['id'] = $row['id']; $_SESSION['nombre'] = $row['nombre']; $_SESSION['rol'] = $row['rol'];
            header("Location: dashboard.php"); exit();
        } else $error = "Contraseña incorrecta.";
    } else $error = "Usuario no existe o inactivo.";
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | MASA TRANSPORTES</title>
    <!-- Favicon actualizado -->
    <link rel="icon" href="assets/img/masa.jpeg" type="image/jpeg">
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --masa-dark: #2B3363; 
            --masa-teal: #2DB5BA; 
        }

        .fade-in { animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        /* El overlay azul oscuro que hará que la foto de los camiones se vea premium */
        .bg-masa-overlay {
            background: linear-gradient(135deg, rgba(43, 51, 99, 0.90) 0%, rgba(20, 25, 50, 0.85) 100%);
        }

        .btn-masatrans { 
            background-color: var(--masa-dark); 
            color: white; 
            border: none; 
            transition: all 0.3s ease; 
        }
        .btn-masatrans:hover { 
            background-color: var(--masa-teal); 
            transform: translateY(-3px); 
            box-shadow: 0 8px 15px rgba(45, 181, 186, 0.3); 
            color: white; 
        }
        
        .form-control:focus {
            border-color: var(--masa-teal) !important;
            box-shadow: 0 0 0 0.25rem rgba(45, 181, 186, 0.2) !important;
        }

        .toggle-password { cursor: pointer; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; color: #6c757d; font-size: 1.2rem; transition: 0.2s; }
        .toggle-password:hover { color: var(--masa-teal); }

        .glass-pillar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px 10px;
            transition: all 0.3s ease;
        }
        .glass-pillar:hover { 
            background: rgba(45, 181, 186, 0.15); 
            border-color: rgba(45, 181, 186, 0.3);
            transform: translateY(-5px); 
        }
        .icon-brand { color: var(--masa-teal); }
    </style>
</head>
<body class="vh-100 overflow-hidden bg-light">

    <div class="row h-100 g-0">
        
        <!-- LADO IZQUIERDO: FONDO CON CAMION.JPG -->
        <div class="col-lg-6 d-none d-lg-flex flex-column align-items-center justify-content-center position-relative text-white text-center" 
             style="background: url('assets/img/camion.jpg') center/cover;">
            
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-masa-overlay"></div>
            
            <div class="position-relative z-1 px-5 w-100 fade-in">
                <!-- LOGO CENTRAL: MASA.JPEG -->
                <img src="assets/img/masa.jpeg" alt="Logo MASA" height="110" class="mb-4 shadow" style="border-radius: 16px;">
                
                <h1 class="fw-bold display-5 mb-2">Plataforma VIGIA</h1>
                <p class="lead opacity-75 mb-5">Innovación en Logística y Desarrollo Humano</p>

                <div class="row justify-content-center px-4 fade-in delay-1">
                    <div class="col-4">
                        <div class="glass-pillar">
                            <i class="bi bi-truck display-6 icon-brand"></i>
                            <div class="small mt-2 fw-semibold">Flota</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="glass-pillar">
                            <i class="bi bi-people-fill display-6 icon-brand"></i>
                            <div class="small mt-2 fw-semibold">Talento</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="glass-pillar">
                            <i class="bi bi-shield-check display-6 icon-brand"></i>
                            <div class="small mt-2 fw-semibold">HSEQ</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LADO DERECHO (Formulario) -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white p-4">
            <div class="w-100 fade-in delay-2" style="max-width: 400px;">
                
                <div class="text-center text-lg-start mb-4">
                    <div class="d-lg-none mb-3 fs-3" style="color: var(--masa-teal);">
                        <i class="bi bi-truck me-2"></i><i class="bi bi-shield-check"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-1">Bienvenido a la ruta</h2>
                    <p class="text-muted small">Ingresa tus credenciales para administrar la operación.</p>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger py-2 small fw-medium rounded-3 border-0 bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    
                    <div class="form-floating mb-3 shadow-sm rounded">
                        <input type="text" name="usuario" class="form-control border-0 bg-light" id="usuario" placeholder="Usuario" required autofocus>
                        <label for="usuario" class="text-muted"><i class="bi bi-person-badge me-2"></i>Usuario asignado</label>
                    </div>

                    <div class="form-floating mb-4 position-relative shadow-sm rounded">
                        <input type="password" name="password" class="form-control border-0 bg-light pe-5" id="password" placeholder="Contraseña" required>
                        <label for="password" class="text-muted"><i class="bi bi-key me-2"></i>Clave de acceso</label>
                        <i class="bi bi-eye-slash toggle-password" id="toggleBtn"></i>
                    </div>

                    <button type="submit" name="login" class="btn btn-masatrans btn-lg w-100 fw-bold fs-6 py-3 rounded-3">
                        Ingresar a Plataforma <i class="bi bi-box-arrow-in-right ms-2"></i>
                    </button>
                </form>

                <p class="text-center text-muted mt-5 small">
                    &copy; <?= date("Y") ?> MASA TRANSPORTES<br>
                    <span class="opacity-50" style="font-size: 0.7rem;">Motor de Operaciones & RRHH</span>
                </p>

            </div>
        </div>

    </div>

    <script>
        document.getElementById('toggleBtn').addEventListener('click', function () {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>