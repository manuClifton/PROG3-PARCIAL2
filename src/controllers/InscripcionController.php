<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;
use App\Components\Resultado;
use \Firebase\JWT\JWT;

$app = AppFactory::create();

class InscripcionController{

    public function getALL(Request $request, Response $response, $args) {

        $inscripcion =  Inscripcion::where('materia_id', $args['idMateria'] )->get();

        $alumnos = [];
        for ($i=0; $i < count($inscripcion); $i++) { 
            if($inscripcion[$i] ){
                $nombre =  User::where('id', $inscripcion[$i]->alumno_id)->first();
                array_push($alumnos, $nombre->nombre);
            }
        }


        $response->getBody()->write(json_encode($alumnos));
        return $response;
    }

    public function addOne(Request $request, Response $response, $args) {

        echo "ENTRE";

        $Key = "segundoparcial";
        $token = $_SERVER['HTTP_TOKEN'];
        $decode = JWT::decode($token,$Key,array('HS256'));    
        //var_dump($decode);
        //echo $decode->id;
        //die();
        $incripcion = new Inscripcion;

        $existMateria =  Materia::where('id', $args['idMateria'] )->first();
        //echo $existMateria;
        //die();
        if(empty($existMateria)){ // si  existe
            $result = new Resultado(false,"ERROR: NO EXISTE LA MATERIA", 500);
            $response->getBody()->write(json_encode($result));
        }else{

            if($existMateria->vacantes > 0){
                $incripcion->alumno_id = $decode->id;
                $incripcion->materia_id = $existMateria->id;
    
                $incripcion->save();

                $existMateria->vacantes --;
                $existMateria->save();
                
                $result = new Resultado(true,$incripcion, 201);
                $response->getBody()->write(json_encode($result));

            }else{
                $result = new Resultado(false,"ERROR: NO HAY CUPOS", 500);
                $response->getBody()->write(json_encode($result));
            }
           
        }
        

        return $response;
    }

}