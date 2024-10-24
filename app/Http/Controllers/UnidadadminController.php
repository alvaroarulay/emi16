<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unidadadmin;
use XBase\TableCreator;
use XBase\TableEditor;
use XBase\TableReader;

class UnidadadminController extends Controller
{
    protected $unidadadmin;

    public function __construct(Unidadadmin $unidadadmin)
    {
        $this->unidadadmin = $unidadadmin;
    }
    public function index()
    {
        $unidad = Unidadadmin::All();
        return [
            'unidad' => $unidad
        ];
    }
    public function update(Request $request){
      
        try {
            $codcont = Unidadadmin::find($request->id);
            $codcont->unidad=$request->unidad;
            $codcont->descrip=$request->descripcion;
            $codcont->estado=$request->estado;
            $codcont->save();
         } catch (Exception $e) {
             return response()->json(['message' => 'Excepción capturada: '+  $e->getMessage()]);
         }
         
         return response()->json(['message' => 'Datos Actualizados Correctamente!!!']);
    }
    public function selectUnidad(){
        $unidad = Unidadadmin::select('id','unidad','descrip')->where('estado','=',1)->get();
        return['unidad'=>$unidad];
    }
}
