<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auxiliares;
use App\Models\Unidadadmin;
use XBase\TableCreator;
use XBase\TableEditor;
use XBase\TableReader;

class AuxiliaresController extends Controller
{
   
    public function index(Request $request)
    {
        $totalaux = Auxiliares::where('auxiliar.codcont','=',$request->codcont)->count();
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $auxiliares = Auxiliares::join('codcont','auxiliar.codcont','=','codcont.codcont')
            ->select('auxiliar.id','auxiliar.codaux','auxiliar.nomaux','codcont.nombre','codcont.codcont')
            ->where('auxiliar.codcont','=',$request->codcont)->get();
        }
        else{
            $auxiliares = Auxiliares::join('codcont','auxiliar.codcont','=','codcont.codcont')
            ->select('auxiliar.id','auxiliar.codaux','auxiliar.nomaux','codcont.nombre','codcont.codcont')
            ->where('auxiliar.codcont','=',$request->codcont)
            ->where($criterio, 'like', '%'. $buscar . '%')->orderBy('id', 'desc')->get();
        }
        return [
            'auxiliares' => $auxiliares,
            'totalaux' => $totalaux
        ];
    }
    public function selectAuxiliar($id){
       
        $unidad = Unidadadmin::where('estado','=','1')->first();

        $auxiliares = Auxiliares::select('id','nomaux','codaux')
            ->where('codcont','=',$id)
            ->get();
        return response()->json(['auxiliares'=>$auxiliares]);
    }
    public function update(Request $request){
        try {
            $auxiliar = Auxiliares::findOrFail($request->id);
            $auxiliar->nomaux = $request->nomaux;
            $auxiliar->codcont = $request->codcont;
            $auxiliar->save();
        } catch (Exception $e) {
            return response()->json(['message' => 'Excepción capturada: '+  $e->getMessage()]);
        }
        
        return response()->json(['message' => 'Datos Actualizados Correctamente!!!']);
    }
}
