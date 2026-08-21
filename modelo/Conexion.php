<?php
//conexiona usar en el proyecto es: PDO

class Conexion{
    private static $conexion = null;
    private function __construct(){

    }

    public static function Conectar(){
        $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        self::$conexion = new PDO('mysql:host=localhost;dbname=clave','root','',$pdo_options);
        return self::$conexion;
    }

    static function Desconectar(&$conn){
        $conn = null;
    }
}

$BaseDatos = Conexion::Conectar();//probar la cadena de conexion

?>