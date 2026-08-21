<?php
require_once('controlador/ControladorAcceso.php');
$ControladorAcceso = new ControladorAcceso();
$ControladorRegistrar = new ControladorRegistro();


//INGRESAR
if (isset($_REQUEST['Validar'])) {
    $ControladorAcceso->ValidarAcceso($_REQUEST['DocumentoUsua'], $_REQUEST['ClaveUsuario']);
}

//REGISTRAR

if(isset($_REQUEST['botonRegistrar'])){
    $ControladorRegistrar->registrarRegistro();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- REDISEÑO 2026: Fuentes modernas Outfit + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- REDISEÑO 2026: Estilos de login modernizados -->
    <link rel="stylesheet" href="css/ingresar.css">
    <!-- REDISEÑO 2026: Spline 3D Viewer - Elementos 3D interactivos -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.12.71/build/spline-viewer.js"></script>
    <title>Clave del Peluquero - Iniciar Sesión</title>
    <style>
        /* Estilos adicionales para Spline 3D interactivo */
        spline-viewer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0; /* Detrás del wrapper */
        }
        body {
            /* Remover fondo anterior si spline está activo. */
            background: transparent !important;
        }
    </style>
</head>
<body>
 <!-- Spline de fondo usando la misma referencia de diseño moderno -->
 <spline-viewer url="https://prod.spline.design/WLSahY8QztWhPsTP/scene.splinecode"></spline-viewer>

 <div class="wrapper">
    <nav class="nav">
        <div class="nav-menu" id="navMenu"></div>
        <div class="nav-button">
            <button class="btn white-btn" id="loginBtn" onclick="login()">Ingresa</button>
            <button class="btn" id="registerBtn" onclick="register()">Regístrate</button>
        </div>
        <div class="nav-menu-btn">
            <i class="bx bx-menu" onclick="myMenuFunction()"></i>
        </div>
    </nav>

<!----------------------------- Form box ----------------------------------->    
    <div class="form-box">
        
        <!------------------- login form -------------------------->
        
            <div class="login-container" id="login">
                <div class="top">
                    <span>¿Aún no tienes una cuenta? <a href="#" onclick="register()">Regístrate</a></span>
                    <header>Ingresa</header>
                </div>    


            <form class="form-signin" id="frmAcceso" name="frmAcceso" action="login.php" method="POST">
                <div class="input-box">
                    <input type="text" class="input-field" placeholder="Documento" id="DocumentoUsua" name="DocumentoUsua">
                    <i class="bx bx-user"></i>
                </div>
                <div class="input-box">
                    <input type="password" class="input-field" placeholder="Contraseña" id="ClaveUsuario" name="ClaveUsuario">
                    <i class="bx bx-lock-alt"></i>
                </div>
                <button class="submit" type="submit" name="Validar" title="Botón para iniciar sesión">Ingresar</button>
                <br></br>
            </form>
            <a href="index.php"><button class="back">Volver</button></a>

            
                <div class="two-col">
                    <div class="one">
                        <input type="checkbox" id="login-check">
                        <label for="login-check"> Recuérdame</label>
                    </div>
                    <div class="two">
                        <label><a href="#">¿Olvidaste la contraseña?</a></label>
                    </div>
                </div>
            </div>
        

        <!------------------- registration form -------------------------->
        <div class="register-container" id="register">
            <div class="top">
                <header>Regístrate</header>
            </div>
                <form action="login.php" method="POST">
                    <div class="two-forms">
                        <div class="input-box">
                            <input type="text" class="input-field" placeholder="Nombre" name="NombreUsuario">
                            <i class="bx bx-user"></i>
                        </div>
                        <div class="input-box">
                            <input type="text" class="input-field" placeholder="Apellido" name="ApellidoUsuario">
                            <i class="bx bx-user"></i>
                        </div>
                    </div>
                    <div class="two-forms">
                        <div class="input-box">
                            <select class="input-field" name="TipoDocumento">
                                <option value="" disabled selected>Tipo de documento</option>
                                <option value="Cedula" style="color: black;">Cedula</option>
                                <option value="Tarjeda de identidad"  style="color: black;">Tarjeta de identidad</option>
                                <option value="Pasaporte"  style="color: black;">Pasaporte</option>
                            </select>
                            <i class='bx bx-user'></i>
                        </div>
                        <div class="input-box">
                            <input type="text" class="input-field" placeholder="Documento" name="DocumentoUsua">
                            <i class='bx bx-file'></i>
                        </div>
                    </div>
                    <div class="two-forms">
                        <div class="input-box">
                            <input type="date" class="input-field" name="FechaNacUsua">
                            <i class='bx bx-file'></i>
                        </div>
                        <div class="input-box">
                            <select class="input-field" name="Genero">
                                <option value="" disabled selected>Seleccione su género</option>
                                <option value="Masculino" style="color: black;">Masculino</option>
                                <option value="Femenino"  style="color: black;">Femenino</option>
                                <option value="Prefiero no decirlo"  style="color: black;">Prefiero no decirlo</option>
                            </select>
                            <i class='bx bx-user'></i>
                        </div>
                    </div>
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Celular" name="CelUsua">
                        <i class='bx bx-mobile'></i>
                    </div>
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Email" name="CorreoUsua">
                        <i class="bx bx-envelope"></i>
                    </div>
                    <div class="input-box">
                            <input type="password" class="input-field" placeholder="Contraseña" name="ClaveUsuario">
                            <i class='bx bx-lock'></i>
                    </div>
                    <button class="submit" type="submit" name="botonRegistrar" title="Botón para registrarse">Registrar</button>
                </form>
            <div class="two-col">
                <div class="one">
                    <input type="checkbox" id="register-check">
                    <label for="register-check"> Recuérdame</label>
                </div>
                <div class="two">
                    <label><a href="#">Términos & Condiciones</a></label>
                </div>
            </div>
        </div>

    </div>
</div>   

<script src="js/index.js"></script>
<!-- Importar GSAP para animaciones fluidas profesionales -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
   
   function myMenuFunction() {
        var i = document.getElementById("navMenu");

        if(i.className === "nav-menu") {
        i.className += " responsive";
        } else {
        i.className = "nav-menu";
        }
    }
    var a = document.getElementById("loginBtn");
    var b = document.getElementById("registerBtn");
    var x = document.getElementById("login");
    var y = document.getElementById("register");

    // Animación inicial GSAP
    gsap.from(".nav", {duration: 1, y: -50, opacity: 0, ease: "power3.out"});
    gsap.from(".form-box", {duration: 1.2, scale: 0.9, opacity: 0, delay: 0.2, ease: "back.out(1.5)"});

    function login() {
        gsap.to(x, {left: "4px", opacity: 1, duration: 0.5, ease: "power2.out"});
        gsap.to(y, {right: "-520px", opacity: 0, duration: 0.5, ease: "power2.out"});
        
        a.className += " white-btn";
        b.className = "btn";
    }

    function register() {
        gsap.to(x, {left: "-510px", opacity: 0, duration: 0.5, ease: "power2.out"});
        gsap.to(y, {right: "5px", opacity: 1, duration: 0.5, ease: "power2.out"});
        
        a.className = "btn";
        b.className += " white-btn";
    }
</script>
<!-- Termina REDISEÑO 2026 -->
</body>
</html>