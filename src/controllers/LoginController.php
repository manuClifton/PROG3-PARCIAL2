<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;
use \Firebase\JWT\JWT;
use Slim\Factory\AppFactory;
use App\Components\Resultado;

$app = AppFactory::create();

class LoginController{

    public function login(Request $request, Response $response) {

        $body = $request->getParsedBody();
        $email = $body['email'];
        $clave = $body['clave'];

        $exist =  User::where('email', $email)->first();

        if(!empty($exist)){
           //verigficar contravceña

           //$pass = password_verify( $clave, $exist->clave); // boolean
           //echo $pass;
           $usuario = json_decode($exist);
          // var_dump($usuario);
          // die();
           if($exist->clave == $clave){

            $Key = "segundoparcial";
            $payload = array(   
                "id" => $usuario->id,
                "email" => $usuario->email,
                "nombre" => $usuario->nombre,
                "tipo" => $usuario->tipo
            );
            $jwt = JWT::encode($payload,$Key);

            $result = new Resultado(true,"TOKEN: ". $jwt, 200);
            $response->getBody()->write(json_encode($result));
           }else{
            $result = new Resultado(false,"ERROR: LOGIN INCORRECTO", 500);
            $response->getBody()->write(json_encode($result));
           }
        }else{
            $result = new Resultado(false,"ERROR: EMAIL NO EXISTE", 500);
            $response->getBody()->write(json_encode($result));
        } 
        
        return $response;
    }

}