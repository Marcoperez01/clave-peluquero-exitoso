<?php
require_once('modelo/Usuario.php');
require_once('modelo/Acceso.php');

class ControladorAcceso {

    public function __construct() {}

    public function ValidarAcceso($DocumentoUsua, $ClaveUsuario) {
        $Usuario = new Usuario();
        $Usuario->setDocumentoUsua($DocumentoUsua);
        $Usuario->setClaveUsuario($ClaveUsuario);

        $Acceso = new Acceso();
        $Acceso->ValidarAcceso($Usuario);

        if ($Usuario->getUsuarioLogueado() == 1) {
            session_start();
            session_regenerate_id(true); // 🔐 Protege contra session fixation

            $_SESSION['DocumentoUsua'] = $DocumentoUsua;
            $_SESSION['NombreUsuario'] = $Usuario->getNombreUsuario();
            $_SESSION['ApellidoUsuario'] = $Usuario->getApellidoUsuario();
            $_SESSION['Rol'] = $Usuario->getRol();
            $_SESSION['CorreoUsua'] = $Usuario->getCorreoUsua(); // Si lo necesitas después

            // Generar un token único para esta sesión
            $_SESSION['token_sesion'] = bin2hex(random_bytes(32));

            header("Location:dashboard/index.html");
        } else {
            echo '<script>alert("Documento y/o contraseña incorrectos");</script>';
        }
    }

    public function DestruirSesion() {
        session_destroy();
        header("Location:../");
    }
}

class ControladorRegistro {

    public function __construct() {}

    public function registrarRegistro() {
        $registro = new Usuario();

        $registro->setDocumentoUsua($_REQUEST['DocumentoUsua']);
        $registro->setTipoDocumento($_REQUEST['TipoDocumento']);
        $registro->setGenero($_REQUEST['Genero']);
        $registro->setNombreUsuario($_REQUEST['NombreUsuario']);
        $registro->setApellidoUsuario($_REQUEST['ApellidoUsuario']);
        $registro->setFechaNacUsua($_REQUEST['FechaNacUsua']);
        $registro->setCelUsua($_REQUEST['CelUsua']);
        $registro->setCorreoUsua($_REQUEST['CorreoUsua']);

        $claveHasheada = password_hash($_REQUEST['ClaveUsuario'], PASSWORD_BCRYPT);
        $registro->setClaveUsuario($claveHasheada);

        $crudRegistrar = new Registrar();
        echo $crudRegistrar->registrarRegistro($registro);
    }
}
