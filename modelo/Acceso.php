<?php
date_default_timezone_set('America/Bogota');
require_once('Conexion.php');

class Acceso {

    public function __construct() {}

    public function ValidarAcceso($Usuario) {
        $BaseDatos = Conexion::Conectar();
        $documento = $Usuario->getDocumentoUsua();
        $claveIngresada = $Usuario->getClaveUsuario();

        $sql = $BaseDatos->prepare("SELECT u.NombreUsuario, u.ApellidoUsuario, u.DocumentoUsua, u.Rol, ul.ClaveUsuario, ul.intentos_fallidos, ul.bloqueado_hasta
                                    FROM usuario u
                                    INNER JOIN usuariologin ul ON u.DocumentoUsua = ul.DocumentoUsua
                                    WHERE ul.DocumentoUsua = :DocumentoUsua");
        $sql->bindParam(':DocumentoUsua', $documento);

        try {
            $sql->execute();
            $datos = $sql->fetch(PDO::FETCH_ASSOC);

            $Usuario->setUsuarioLogueado(0); // Por defecto: no logueado

            if (!$datos) {
                echo "<script>alert('Usuario no encontrado');</script>";
                return;
            }

            $ahora = new DateTime();
            if (!empty($datos['bloqueado_hasta']) && $ahora < new DateTime($datos['bloqueado_hasta'])) {
                echo "<script>alert('Usuario bloqueado temporalmente. Intente más tarde');</script>";
                return;
            }

            if (password_verify($claveIngresada, $datos['ClaveUsuario'])) {
                // Login exitoso: Reiniciar intentos
                $reset = $BaseDatos->prepare("UPDATE usuariologin SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE DocumentoUsua = :documento");
                $reset->bindParam(':documento', $documento);
                $reset->execute();

                $Usuario->setUsuarioLogueado(1);
                $Usuario->setNombreUsuario($datos['NombreUsuario']);
                $Usuario->setApellidoUsuario($datos['ApellidoUsuario']);
                $Usuario->setRol($datos['Rol']);
                $Usuario->setDocumentoUsua($datos['DocumentoUsua']);

            } else {
                // Login fallido: aumentar intentos
                $intentos = $datos['intentos_fallidos'] + 1;
                $bloqueo = null;

                if ($intentos >= 3) {
                    $bloqueo = (new DateTime())->modify('+5 minutes')->format('Y-m-d H:i:s');
                }

                $fail = $BaseDatos->prepare("UPDATE usuariologin SET intentos_fallidos = :intentos, bloqueado_hasta = :bloqueo WHERE DocumentoUsua = :documento");
                $fail->bindParam(':intentos', $intentos, PDO::PARAM_INT);
                $fail->bindParam(':bloqueo', $bloqueo);
                $fail->bindParam(':documento', $documento);
                $fail->execute();
            }

        } catch (Exception $e) {
            error_log($e->getMessage());
            echo "<script>alert('Ha ocurrido un error');</script>";
        }

        Conexion::Desconectar($BaseDatos);
    }
}

class Registrar {

    public function __construct() {}

    public function registrarRegistro($registro) {
        $mensaje = "";
        $basedatos = Conexion::Conectar();

        $sql = $basedatos->prepare('INSERT INTO usuario(DocumentoUsua, TipoDocumento, Rol, Genero, NombreUsuario, ApellidoUsuario, FechaNacUsua, CelUsua, CorreoUsua)
                                    VALUES(:DocumentoUsua, :TipoDocumento, "Cliente", :Genero, :NombreUsuario, :ApellidoUsuario, :FechaNacUsua, :CelUsua, :CorreoUsua)');
        $sql->bindValue('DocumentoUsua', $registro->getDocumentoUsua());
        $sql->bindValue('TipoDocumento', $registro->getTipoDocumento());
        $sql->bindValue('Genero', $registro->getGenero());
        $sql->bindValue('NombreUsuario', $registro->getNombreUsuario());
        $sql->bindValue('ApellidoUsuario', $registro->getApellidoUsuario());
        $sql->bindValue('FechaNacUsua', $registro->getFechaNacUsua());
        $sql->bindValue('CelUsua', $registro->getCelUsua());
        $sql->bindValue('CorreoUsua', $registro->getCorreoUsua());

        $sql2 = $basedatos->prepare('INSERT INTO usuariologin(DocumentoUsua, ClaveUsuario)
                                     VALUES(:DocumentoUsua, :ClaveUsuario)');
        $sql2->bindValue('DocumentoUsua', $registro->getDocumentoUsua());
        $sql2->bindValue('ClaveUsuario', $registro->getClaveUsuario());

        try {
            $sql->execute();
            $sql2->execute();
            $mensaje = '<script language="javascript">
                            alert("Registro Exitoso");
                            window.location.href = "login.php";
                        </script>';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "<script>alert('Ya existe un usuario con ese documento.');</script>";
            } else {
                error_log($e->getMessage());
                echo "<script>alert('Ha ocurrido un error. Por favor, inténtalo de nuevo más tarde.');</script>";
            }
        }

        Conexion::Desconectar($basedatos);
        return $mensaje;
    }
}