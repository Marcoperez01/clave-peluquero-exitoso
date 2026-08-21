<?php
require_once('Conexion.php');//incluir conexion
class CrudUsuario{
    public function __construct(){
        
    }

    public function listarUsuario(){
        //establecer la conexion con la base de datos
        $basedatos = Conexion::Conectar();
        //Definir el SQL
        //SQl: Struct Query Languaje: lenguaje estructurado de consulta
        $sql = $basedatos->query('SELECT * FROM usuario');
        //ejecutar a consulta 
        $sql->execute();
        Conexion::Desconectar($basedatos);
        return($sql->fetchAll());//contiene los registros de la consulta
    }


    public function RegistrarUsuario($registro){
        $mensaje = "";
        
        $basedatos = Conexion::Conectar();

        $sql = $basedatos->prepare('INSERT INTO usuario(DocumentoUsua, TipoDocumento, Rol, Genero, NombreUsuario, ApellidoUsuario, FechaNacUsua, CelUsua, CorreoUsua)
                                    VALUES(:DocumentoUsua, :TipoDocumento, :Rol, :Genero, :NombreUsuario, :ApellidoUsuario, :FechaNacUsua, :CelUsua, :CorreoUsua)');
        $sql->bindValue('DocumentoUsua', $registro->getDocumentoUsua());
        $sql->bindValue('TipoDocumento', $registro->getTipoDocumento());
        $sql->bindValue('Rol', $registro->getRol());
        $sql->bindValue('Genero', $registro->getGenero());
        $sql->bindValue('NombreUsuario', $registro->getNombreUsuario());
        $sql->bindValue('ApellidoUsuario', $registro->getApellidoUsuario());
        $sql->bindValue('FechaNacUsua', $registro->getFechaNacUsua());
        $sql->bindValue('CelUsua', $registro->getCelUsua());
        $sql->bindValue('CorreoUsua', $registro->getCorreoUsua());

        $sql2 = $basedatos->prepare('INSERT INTO usuariologin(CorreoUsua,ClaveUsuario)
                                     VALUES(:CorreoUsua, :ClaveUsuario)');
        $sql2->bindValue('CorreoUsua', $registro->getCorreoUsua());
        $sql2->bindValue('ClaveUsuario', $registro->getClaveUsuario());

        try{
            $sql->execute();//ejectar sql
            $sql2->execute();
            $mensaje = '<script language="javascript">
                            alert("Registro Exitoso");
                            window.location.href = "listarUsuario.php";
                        </script>'; 
        }
        catch(Exception $e){
            $mensaje = $e->getMessage();
        }
        Conexion::Desconectar($basedatos);
        return $mensaje;
    }

    public function BuscarUsuario($DocumentoUsua){
        $basedatos = Conexion::Conectar();
        $sql = $basedatos->query("SELECT u.DocumentoUsua, u.TipoDocumento, u.Rol, u.Genero, u.NombreUsuario, u.ApellidoUsuario, u.FechaNacUsua, u.CelUsua, u.CorreoUsua, l.ClaveUsuario FROM usuario u INNER JOIN usuariologin l ON u.CorreoUsua = l.CorreoUsua WHERE u.DocumentoUsua = $DocumentoUsua;");
        $sql->execute(); 
        Conexion::Desconectar($basedatos);
        return $sql->fetch();
    }

    public function ModificarUsuario($registro){
        $mensaje = "";
        $basedatos = Conexion::Conectar();
        $sql = $basedatos->prepare("UPDATE usuario
                                    SET TipoDocumento=:TipoDocumento,
                                    Rol=:Rol,
                                    Genero=:Genero,
                                    NombreUsuario=:NombreUsuario,
                                    ApellidoUsuario=:ApellidoUsuario,
                                    FechaNacUsua=:FechaNacUsua,
                                    CelUsua=:CelUsua,
                                    CorreoUsua=:CorreoUsua
                                    WHERE DocumentoUsua=:DocumentoUsua");
            $sql->bindValue('DocumentoUsua', $registro->getDocumentoUsua());
            $sql->bindValue('TipoDocumento', $registro->getTipoDocumento());
            $sql->bindValue('Rol', $registro->getRol());
            $sql->bindValue('Genero', $registro->getGenero());
            $sql->bindValue('NombreUsuario', $registro->getNombreUsuario());
            $sql->bindValue('ApellidoUsuario', $registro->getApellidoUsuario());
            $sql->bindValue('FechaNacUsua', $registro->getFechaNacUsua());
            $sql->bindValue('CelUsua', $registro->getCelUsua());
            $sql->bindValue('CorreoUsua', $registro->getCorreoUsua());

        $sql2 = $basedatos->prepare("UPDATE usuariologin
                                     SET CorreoUsua=:CorreoUsua,
                                     ClaveUsuario=:ClaveUsuario
                                     WHERE CorreoUsua=:CorreoUsua");
        $sql2->bindValue('CorreoUsua', $registro->getCorreoUsua());
        $sql2->bindValue('ClaveUsuario', $registro->getClaveUsuario());

        try{ 
            $sql->execute();
            $sql2->execute();//ejectar sql
            $mensaje = '<script language="javascript">
                            alert("Modificación Exitosa");
                            window.location.href = "listarUsuario.php";
                        </script>';
        }
        catch(Exception $e){
            $mensaje = $e->getMessage();
        }
        Conexion::Desconectar($basedatos);
        return $mensaje;
    }

    public function EliminarUsuario($DocumentoUsua) {
        $basedatos = Conexion::Conectar();
        $sql = $basedatos->prepare("DELETE FROM usuario WHERE DocumentoUsua = :DocumentoUsua");
    
        try {
            // Usar bindParam para evitar inyecciones SQL
            $sql->bindParam(':DocumentoUsua', $DocumentoUsua, PDO::PARAM_INT);
            $sql->execute();
    
            // Imprimir directamente el script de éxito
            echo '<script language="javascript">
                    alert("Eliminación Exitosa");
                    window.location.href = "listarUsuario.php";
                  </script>';
        } catch (Exception $e) {
            // Si hay un error, mostrarlo
            echo '<script language="javascript">
                    alert("Error: ' . $e->getMessage() . '");
                  </script>';
        }
    
        Conexion::Desconectar($basedatos);
    }
    


}
?>