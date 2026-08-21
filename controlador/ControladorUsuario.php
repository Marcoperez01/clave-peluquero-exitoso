<?php
require_once(__DIR__ . '/../modelo/Usuario.php');
require_once(__DIR__ . '/../modelo/CrudUsuario.php');



class ControladorUsuario{
    
    public function __construct(){}

    public function listarUsuario(){
        $CrudRegistrar = new CrudUsuario();
        return $CrudRegistrar->listarUsuario(); 
    }

    public function RegistrarUsuario(){
        $registro = new Usuario(); //Creando un objeto de la clase Encuesta
        //Setear el objeto con los valores ingresados en el formulario
        $registro->setDocumentoUsua($_REQUEST['DocumentoUsua']);
        $registro->setTipoDocumento($_REQUEST['TipoDocumento']);
        $registro->setRol($_REQUEST['Rol']);
        $registro->setGenero($_REQUEST['Genero']);
        $registro->setNombreUsuario($_REQUEST['NombreUsuario']);
        $registro->setApellidoUsuario($_REQUEST['ApellidoUsuario']);
        $registro->setFechaNacUsua($_REQUEST['FechaNacUsua']);
        $registro->setCelUsua($_REQUEST['CelUsua']);
        $registro->setCorreoUsua($_REQUEST['CorreoUsua']);
        $registro->setClaveUsuario(sha1($_REQUEST['ClaveUsuario']));
        $crudRegistrar = new CrudUsuario();
        echo $crudRegistrar->RegistrarUsuario($registro);//Llamado al método del crud  
    }

    public function EditarUsuario($DocumentoUsua){
        $registro = new Usuario(); //Creando un objeto de la clase encuesta
        //Setear el objeto con los valores ingresados en el formulario
        $registro->setDocumentoUsua($DocumentoUsua);
        header("Location:EditarUsuario.php?DocumentoUsua=$DocumentoUsua");
    }

    public function BuscarUsuario($DocumentoUsua){
        $crudRegistrar = new CrudUsuario();
        $DatosUsuario = $crudRegistrar->BuscarUsuario($DocumentoUsua);
        $registro = new Usuario();
        $registro->setDocumentoUsua($DatosUsuario['DocumentoUsua']);
        $registro->setTipoDocumento($DatosUsuario['TipoDocumento']);
        $registro->setRol($DatosUsuario['Rol']);
        $registro->setGenero($DatosUsuario['Genero']); 
        $registro->setNombreUsuario($DatosUsuario['NombreUsuario']);
        $registro->setApellidoUsuario($DatosUsuario['ApellidoUsuario']);        
        $registro->setFechaNacUsua($DatosUsuario['FechaNacUsua']);
        $registro->setCelUsua($DatosUsuario['CelUsua']);
        $registro->setCorreoUsua($DatosUsuario['CorreoUsua']);
        $registro->setClaveUsuario($DatosUsuario['ClaveUsuario']);
        return $registro; //Retorna un objeto con los datos del producto
    }

    public function ModificarUsuario() {
        session_start();
    
        // Validación del token de sesión
        if (
            !isset($_SESSION['token_sesion']) ||
            !isset($_POST['token_sesion']) ||
            $_POST['token_sesion'] !== $_SESSION['token_sesion']
        ) {
            session_destroy();
            header("Location: ../index.php");
            exit();
        }
    
        // Si el token es válido, continúa con el procesamiento del formulario
        $registro = new Usuario();
        $registro->setDocumentoUsua($_REQUEST['DocumentoUsua']);
        $registro->setTipoDocumento($_REQUEST['TipoDocumento']);
        $registro->setRol($_REQUEST['Rol']);
        $registro->setGenero($_REQUEST['Genero']);
        $registro->setNombreUsuario($_REQUEST['NombreUsuario']);
        $registro->setApellidoUsuario($_REQUEST['ApellidoUsuario']);
        $registro->setFechaNacUsua($_REQUEST['FechaNacUsua']);
        $registro->setCelUsua($_REQUEST['CelUsua']);
        $registro->setCorreoUsua($_REQUEST['CorreoUsua']);
        $registro->setClaveUsuario(sha1($_REQUEST['ClaveUsuario']));
    
        $crudRegistrar = new CrudUsuario();
        echo $crudRegistrar->ModificarUsuario($registro);
    }
    

    public function EliminarUsuario($DocumentoUsua) {
        session_start();
    
        if (
            !isset($_SESSION['token_sesion']) ||
            !isset($_POST['token_sesion']) ||
            $_POST['token_sesion'] !== $_SESSION['token_sesion']
        ) {
            session_destroy();
            header("Location: ../index.php");
            exit();
        }
    
        $crudRegistrar = new CrudUsuario();
        $crudRegistrar->EliminarUsuario($DocumentoUsua); // ejecuta la eliminación
    
    }
    
}
?>