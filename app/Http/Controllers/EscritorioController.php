<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Actual;
use App\Models\CodigoContable;
use App\Models\Auxiliares;
use App\Models\Oficinas;
use App\Models\Responsables;
use App\Models\User;

class EscritorioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $activos = Actual::count();
       $auxiliares = Auxiliares::count();
       $oficinas = Oficinas::count();
       $responsables = Responsables::count();
       $users = User::count();
       return response()->json(['activos'=>$activos,'auxiliares'=>$auxiliares,'oficinas'=>$oficinas,'responsables'=>$responsables, 'users'=>$users]);
    }

    public function grafica1()
    {
        $estados = Actual::join('estado','estado.id','=','actual.codestado')->select('estado.nomestado',DB::raw('count(*) as valor'))->groupBy('actual.codestado')->get();
        return response()->json(['estados'=>$estados]);
    }

    public function grafica2()
    {
        $gcontable = Actual::join('codcont','actual.codcont', '=', 'codcont.codcont')
        ->select('codcont.nombre',DB::raw('count(*) as valor'))
        ->groupBy('codcont.nombre')
        ->orderBy('valor', 'DESC')->take(10)->get();
        return response()->json(['gcontable'=>$gcontable]);
    }
}
