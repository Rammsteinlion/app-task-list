<?php

namespace App\Config;

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Bulletproof\Image;

class Security{

    private static $jwt_data;

    final public static function  secretKey(){
        $dotenv = Dotenv::createImmutable(dirname(__DIR__,2));
        $dotenv->load();
        return $_ENV['SECRET_KEY'];
    }

    final public static function  createPassword(string $pw):string{
        $pass = password_hash($pw,PASSWORD_DEFAULT);
        return $pass;
    }

    final public static function validatePassword(string $pw, string $pwh){
        if(password_verify($pw,$pwh)){
            return true;
        }{
            error_log('Contrasena incorrecta');
            return false;
        }
    }

    final public static function createTokenJwt(string $key, array $data){
        $payload = array(
             'iat' =>time(), //tiempo en el que se crel jwt
             'exp' =>time() + (10), //tiempo de expiracion del token
             'data'=> $data, //datos a encriptar
        );

        //crear el jwt
        $jwt = JWT::encode($payload, $key,'HS256');
        return $jwt;
    }

    final public static function validateJwt(array $token, string $key){
        //validar cabecera jwt
        if(!isset($token['Authorization'])) die(json_encode(ResponseHttp::status400())); exit;
        
        try {
            $jwt = explode(" ", $token['Authorization']);
            $data = JWT::decode($jwt, $key);
            self::$jwt_data = $data;
            return $data;
            exit;
        } catch (\Exception $e) {
            error_log('Token invalido o expiro');
            die(json_encode(ResponseHttp::status401('Token invalido o expirado')));
            throw $e;
        }
        
    }

    //decodificar los datos del jwt, rerotnar los datos que tieine jwt en array asociativoi
    final public static function getDataJwt(){
        $jwt_decode_array = json_decode(json_encode(self::$jwt_data), true);
        return $jwt_decode_array['data'];
        exit;
    }

}


?>