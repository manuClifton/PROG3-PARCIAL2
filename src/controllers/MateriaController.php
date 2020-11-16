<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Models\Materia;
use App\Components\Resultado;


$app = AppFactory::create();

class MateriaController{

    public function getALL(Request $request, Response $response) {
        $rta = Materia::get();

        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function addOne(Request $request, Response $response) {
        $parserBody = $request->getParsedBody();
        $materia = new Materia;

        if( !isset($_POST['materia']) || !isset($_POST['cuatrimestre']) || !isset($_POST['cupos']) ){
            $result = new Resultado(false,"ERROR: FALTAN DATOS", 500);
            $response->getBody()->write(json_encode($result));
        }else{
            if( empty($parserBody['materia'])  || empty($parserBody['cuatrimestre']) || empty($parserBody['cupos']) ){
                $result = new Resultado(false,"ERROR: DATOS INVALIDOS", 500);
                $response->getBody()->write(json_encode($result));
            }else{
                if($parserBody['cuatrimestre'] == 1 || $parserBody['cuatrimestre'] == 2 || $parserBody['cuatrimestre'] == 3 ||$parserBody['cuatrimestre'] == 4 ){
                    $exist =  Materia::where('materia', $parserBody['materia'])->get();
                    $materiaExistente = json_decode($exist);
                    //var_dump($materiaExistente);
                    //die();
                    $cuatrimetres = [];
    
                    for ($i=0; $i < count($materiaExistente); $i++) { 
                        array_push($cuatrimetres, $materiaExistente[$i]->cuatrimestre);
                    }
    
                    $noValidCuatri = in_array($_POST['cuatrimestre'], $cuatrimetres);
    
                    
    
                    if(!empty($exist) && $noValidCuatri){ // si  existe
                        $result = new Resultado(false,"ERROR: YA EXISTE LA MATERIA EN EL CUATRIMESTRE ".$_POST['cuatrimestre']." CON EL PROFESOR ".$_POST['profesor'] , 500);
                        $response->getBody()->write(json_encode($result));
                    }else{
                        $materia->materia = $parserBody['materia'];
                        $materia->cuatrimestre =  $parserBody['cuatrimestre'];
                        $materia->vacantes = $parserBody['cupos'];
    
                         $materia->save();
                        
                        $result = new Resultado(true,$materia, 201);
                        $response->getBody()->write(json_encode($result));
                    }
                }else{
                    $result = new Resultado(false,"ERROR: CUATRIMESTRE INVALIDO ELIGA 1, 2, 3 O 4", 500);
                    $response->getBody()->write(json_encode($result));
                }
            } 
        }
        return $response;
    }

}