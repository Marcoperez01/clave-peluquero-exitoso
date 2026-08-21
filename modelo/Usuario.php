<?php

class Usuario{
    private $CorreoUsua;
    private $ClaveUsuario;
    private $UsuarioLogueado;

    private $NombreUsuario;
    private $ApellidoUsuario;
    private $Rol;
    private $Genero;
    private $FechaNacUsua;
    private $CelUsua;
    private $TipoDocumento;
    private $DocumentoUsua;

    public function __construct(){

    }


    public function setCorreoUsua($CorreoUsua){
        $this->CorreoUsua = $CorreoUsua;
    }

    public function setClaveUsuario($ClaveUsuario){
        $this->ClaveUsuario = $ClaveUsuario;
    }

    public function setUsuarioLogueado($UsuarioLogueado){
        $this->UsuarioLogueado = $UsuarioLogueado;
    }

    public function setNombreUsuario($NombreUsuario){
        $this->NombreUsuario = $NombreUsuario;
    }

    public function setApellidoUsuario($ApellidoUsuario){
        $this->ApellidoUsuario = $ApellidoUsuario;
    }

    public function setRol($Rol){
        $this->Rol = $Rol;
    }

    public function setDocumentoUsua($DocumentoUsua){
        $this->DocumentoUsua = $DocumentoUsua;
    }

    public function setTipoDocumento($TipoDocumento){
        $this->TipoDocumento = $TipoDocumento;
    }

    public function setCelUsua($CelUsua){
        $this->CelUsua = $CelUsua;
    }

    public function setFechaNacUsua($FechaNacUsua){
        $this->FechaNacUsua = $FechaNacUsua;
    }

    public function setGenero($Genero){
        $this->Genero = $Genero;
    }


    //GET

    public function getCorreoUsua(){
        return $this->CorreoUsua;
    }

    public function getClaveUsuario(){
        return $this->ClaveUsuario;
    }

    public function getUsuarioLogueado(){
        return $this->UsuarioLogueado;
    }

    public function getNombreUsuario(){
        return $this->NombreUsuario;
    }

    public function getApellidoUsuario(){
        return $this->ApellidoUsuario;
    }

    public function getRol(){
        return $this->Rol;
    }

    public function getDocumentoUsua(){
        return $this->DocumentoUsua;
    }

    public function getTipoDocumento(){
        return$this->TipoDocumento;
    }

    public function getCelUsua(){
        return $this->CelUsua;
    }

    public function getFechaNacUsua(){
        return $this->FechaNacUsua;
    }

    public function getGenero(){
        return $this->Genero;
    }



}

?>
