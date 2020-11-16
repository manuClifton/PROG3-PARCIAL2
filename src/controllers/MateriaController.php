<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Models\Materia;
use App\Models\User;
use App\Components\Resultado;
use \Firebase\JWT\JWT;

$app = AppFactory::create();

class MateriaController{

    public function getALL(Request $request, Response $response) {
      

        $Key = "segundoparcial";
        $token = $_SERVER['HTTP_TOKEN'];
        $decode = JWT::decode($token,$Key,array('HS256'));    
        //var_dump($decode);
        //die();
        switch($decode->tipo){
            case 1:
                //Si es alumno puede ver en qué materias está inscripto
                // BUSCAR EN MODEL INSCRIPCCIONES
                echo "soy alumno";
            break;
            case 2:
                // si es profesor piede ver que materias tiene a cargo
                //$rta = Materia::where('');// first();
                $rta =  Materia::where('profesor_id', $decode->id)->get();
                $result = new Resultado(true, $rta, 200);
                $response->getBody()->write(json_encode($result));
            break;
            case 3:
                $rta = Materia::get();
                $result = new Resultado(true, $rta, 200);
                $response->getBody()->write(json_encode($result)); 
            break;
        } 

        return $response;
    }

    public function addOne(Request $request, Response $response) {
        $parserBody = $request->getParsedBody();
        $materia = new Materia;

        if( !isset($_POST['materia']) || !isset($_POST['cuatrimestre']) || !isset($_POST['vacantes'])  || !isset($_POST['profesor']) ){
            $result = new Resultado(false,"ERROR: FALTAN DATOS", 500);
            $response->getBody()->write(json_encode($result));
        }else{
            if( empty($parserBody['materia'])  || empty($parserBody['cuatrimestre']) || empty($parserBody['vacantes']) || empty($parserBody['profesor']) ){
                $result = new Resultado(false,"ERROR: DATOS INVALIDOS", 500);
                $response->getBody()->write(json_encode($result));
            }else{
                $exist =  Materia::where('materia', $parserBody['materia'])->get();
                $materiaExistente = json_decode($exist);
                //var_dump($materiaExistente);
                //die();
                $cuatrimetres = [];

                for ($i=0; $i < count($materiaExistente); $i++) { 
                    array_push($cuatrimetres, $materiaExistente[$i]->cuatrimestre);
                }

                $noValidCuatri = in_array($_POST['cuatrimestre'], $cuatrimetres);

                $profes = [];

                for ($i=0; $i < count($materiaExistente); $i++) { 
                    array_push($profes, $materiaExistente[$i]->profesor_id);
                }

                $noValidProfe = in_array($_POST['profesor'], $profes);

                if(!empty($exist) && $noValidCuatri && $noValidProfe){ // si  existe
                    $result = new Resultado(false,"ERROR: YA EXISTE LA MATERIA EN EL CUATRIMESTRE ".$_POST['cuatrimestre']." CON EL PROFESOR ".$_POST['profesor'] , 500);
                    $response->getBody()->write(json_encode($result));
                }else{
                    $materia->materia = $parserBody['materia'];
                    $materia->cuatrimestre =  $parserBody['cuatrimestre'];
                    $materia->vacantes = $parserBody['vacantes'];
                    $materia->profesor_id = $parserBody['profesor'];

                     $materia->save();
                    
                    $result = new Resultado(true,$materia, 201);
                    $response->getBody()->write(json_encode($result));
                }
            } 
        }
        return $response;
    }

}