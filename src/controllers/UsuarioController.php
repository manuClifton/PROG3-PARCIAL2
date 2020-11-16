<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Models\User;
use App\Components\Resultado;
use \Firebase\JWT\JWT;

$app = AppFactory::create();

class UsuarioController{

    public function getALL(Request $request, Response $response) {
        $rta = User::get();

        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function addOne(Request $request, Response $response) {
        $parserBody = $request->getParsedBody();
        $user = new User;

        if( !isset($_POST['email']) || !isset($_POST['clave']) || !isset($_POST['tipo']) || !isset($_POST['nombre']) ){
            $result = new Resultado(false,"ERROR: FALTAN DATOS", 500);
            $response->getBody()->write(json_encode($result));
        }else{
            if( empty($parserBody['email'])  || empty($parserBody['clave']) || empty($parserBody['tipo']) || empty($_POST['nombre'])){

                $response->getBody()->write(json_encode("ERROR: DATOS INVALIDOS"));

            }else{
                if(strlen($_POST['clave']) >= 4){
                    if($parserBody['tipo'] == "admin" || $parserBody['tipo'] == "profesor" || $parserBody['tipo'] == "alumno"){

                        $existEmail =  User::where('email', trim($parserBody['email']))->first();
                        $existNombre =  User::where('nombre', trim($parserBody['nombre']))->first();
                        //$lastLegajo = User::orderBy('legajo', 'DESC')->first()['legajo'];
    
    
                        if(empty($existEmail) && empty($existNombre)){ // si no existe
                            $user->email = trim(strtolower($parserBody['email']));
                            //$user->clave = password_hash($parserBody['clave'], PASSWORD_BCRYPT);
                            //despues usar pass:verify
                            $user->clave = $parserBody['clave'];
                            $user->tipo = $parserBody['tipo'];
                            $user->nombre = trim(strtolower($parserBody['nombre']));
                           /* if(!$lastLegajo){
                                $user->legajo = 1000;
                            }else{
                                $user->legajo = $lastLegajo + 100;
                            }*/
                        
                            try {
                                $user->save();
                                $result = new Resultado(true, $user, 201);
                                $response->getBody()->write(json_encode($result)); // save devuelve true o false
                            } catch (\Throwable $th) {
                                $result = new Resultado(false,"ERROR: NO SE PUDO GUARDAR", 500);
                                $response->getBody()->write(json_encode($result));
                            }
                        }else{
                            $result = new Resultado(false,"ERROR: EMAIL O NOMBRE EXISTENTE ", 500);
                            $response->getBody()->write(json_encode($result));
                        } 
                    }else{
                        $result = new Resultado(false,"ERROR: TIPO DE USUARIO INVALIDO", 500);
                        $response->getBody()->write(json_encode($result));
                    }
                }else{
                    $result = new Resultado(false,"ERROR: CLAVE MUY CORTA", 500);
                    $response->getBody()->write(json_encode($result));
                }
                
            } 
        }
        return $response;
    }

    public function updateOne(Request $request, Response $response, $args)
    {
        $Key = "segundoparcial";
        $token = $_SERVER['HTTP_TOKEN'];
        $decode = JWT::decode($token,$Key,array('HS256'));     

        $user =  User::where('legajo', $args['legajo'])->first();
        $parserBody = $request->getParsedBody();
        //var_dump($parserBody);
        //die();
        if($user){

            switch($decode->tipo){
                case 1:
                    if( isset($_PUT['email']) || $parserBody['email'] != ''){
                        $user->email = $parserBody['email'];
                        try {
                            $user->save();
                            $result = new Resultado(true, $user, 201);
                            $response->getBody()->write(json_encode($result)); // save devuelve true o false
                        } catch (\Throwable $th) {
                            $result = new Resultado(false,"ERROR: NO SE PUDO GUARDAR", 500);
                            $response->getBody()->write(json_encode($result));
                        }
                    }else{
                        $result = new Resultado(false,"ERROR: FALTAN DATOS", 500);
                        $response->getBody()->write(json_encode($result));
                    }
                break;
                case 2:
                    
                case 3:
            }   
        }else{
            $result = new Resultado(false,"ERROR: NO EXISTE EL LEGAJO", 500);
            $response->getBody()->write(json_encode($result));
        }
        
        return $response;
    }

    public static function updateAlumno($args){
        
    }

}