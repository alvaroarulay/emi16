<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use App\Models\Responsables;
use App\Models\Unidadadmin;
use App\Models\Actual;
use XBase\TableCreator;
use XBase\TableEditor;
use XBase\TableReader;

class ResponsablesController extends Controller
{
    public function index(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $total = Responsables::count('id');
        if ($buscar==''){
            $responsables = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
            ->select('resp.id','resp.codofic','resp.codresp','resp.nomresp','resp.cargo','resp.estado',
            'resp.ci','oficina.nomofic','resp.api_estado','resp.cod_exp')->distinct('resp.id')->paginate(10);
        }
        else{
            $responsables = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
            ->select('resp.id','resp.codofic','resp.codresp','resp.nomresp','resp.cargo','resp.estado',
            'resp.ci','oficina.nomofic','resp.api_estado','resp.cod_exp')->distinct('resp.id')
            ->where($criterio, 'like', '%'. $buscar . '%')->orderBy('id', 'desc')
            ->paginate(10);
        }
        

        return [
            'pagination' => [
                'total'        => $responsables->total(),
                'current_page' => $responsables->currentPage(),
                'per_page'     => $responsables->perPage(),
                'last_page'    => $responsables->lastPage(),
                'from'         => $responsables->firstItem(),
                'to'           => $responsables->lastItem(),
            ],
            'responsables' => $responsables,
            'total' => $total
        ];
    }
    public function buscarRespActivo(Request $request){
        
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $responsables = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
            ->select('resp.id','resp.codofic','resp.codresp','resp.nomresp','resp.cargo','resp.estado',
            'resp.ci','oficina.nomofic','resp.api_estado','resp.cod_exp')
            ->where('resp.api_estado','=',1)->paginate(10);
        }
        else{
            $responsables = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
            ->select('resp.id','resp.codofic','resp.codresp','resp.nomresp','resp.cargo','resp.estado',
            'resp.ci','oficina.nomofic','resp.api_estado','resp.cod_exp')
            ->where('resp.api_estado','=',1)
            ->where($criterio, 'like', '%'. $buscar . '%')->orderBy('id', 'desc')
            ->paginate(10);
        }
        

        return [
            'pagination' => [
                'total'        => $responsables->total(),
                'current_page' => $responsables->currentPage(),
                'per_page'     => $responsables->perPage(),
                'last_page'    => $responsables->lastPage(),
                'from'         => $responsables->firstItem(),
                'to'           => $responsables->lastItem(),
            ],
            'responsables' => $responsables
        ];
    }
    public function store(Request $request)
    {
        $unidad = Unidadadmin::select('unidad')->where('estado','=','1')->get();
        $fecha = Carbon::now()->format('Ymd');

   try { 
        
            $codofic = Responsables::where('codofic','=',$request->codofic)->where('unidad','=',$request->unidad)->count();
            $responsable = new Responsables();
            $responsable->entidad='170';
            $responsable->unidad=$request->unidad;
            $responsable->codofic = $request->codofic;
            $responsable->codresp = $codofic + 1;
            $responsable->nomresp = $request->nomresp;
            $responsable->cargo = $request->cargo;
            $responsable->observ = $request->observ;
            $responsable->ci = $request->ci;
            $responsable->feult = $fecha;
            $responsable->usuar = \Auth::user()->username;
            $responsable->cod_exp = $request->expedido;
            $responsable->api_estado = 1;
            $responsable->estado = $request->estado;
            $responsable->custodio = 0;
            $responsable->save();

            return response()->json(['message' => 'Datos Guardados Correctamente!!!']);   
        } catch (Exception $e) {
            return response()->json(['message' => 'Excepción capturada: '+  $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        try {
            $responsable = Responsables::findOrFail($request->id);
            $responsable->nomresp = $request->nomresp;
            $responsable->cargo = $request->cargo;
            $responsable->ci = $request->ci;
            $responsable->cod_exp = $request->cod_exp;
            $responsable->api_estado = $request->api_estado;
            $responsable->save();
      
        } catch (Exception $e) {
            return response()->json(['message' => 'Excepción capturada: '+  $e->getMessage()]);
        }
        
        return response()->json(['message' => 'Datos Actualizados Correctamente!!!']);
    }
    public function actualizarDatos(){
        $table = new TableReader(public_path('vsiaf/dbfs/RESP.DBF'),['encoding' => 'cp1252']);
        $responsables=Responsables::count();
        $contador = 0;
      
        while ($record = $table->nextRecord()) {
            $contador ++;
            if($responsables < $contador){
                DB::table('resp')->insert([
                'entidad' =>$record->get('entidad'),
                'unidad' =>$record->get('unidad'),
                'codofic' =>$record->get('codofic'),
                'codresp' =>$record->get('codresp'),
                'nomresp' =>$record->get('nomresp'),
                'cargo' =>$record->get('cargo'),
                'observ' =>$record->get('observ'),
                'ci' =>$record->get('ci'),
                'feult' =>$record->get('feult'),
                'usuar' =>$record->get('usuar'),
                'cod_exp' =>$record->get('cod_exp'),
                'api_estado' =>$record->get('api_estado'),
                ]);
            }            
        }
        $table->close();
        if($responsables == $contador){
            return response()->json(['message' => 'No hay Registros Nuevos!!!']);
        } 
        else{
            return response()->json(['message' => 'Datos Actualizados Correctamente!!!']);
        }
      }
    public function buscarResponsable(Request $request){

        //if (!$request->ajax()) return redirect('/');

        $filtro = $request->filtro;
        $responsable = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
        ->where('resp.ci','=', $filtro)
        ->select('resp.id','resp.nomresp','resp.cargo','oficina.nomofic','resp.api_estado','resp.codresp','resp.codofic')->first();
        return response()->json(['responsable' => $responsable]);
    }
    public function buscarRespxcodigo(Request $request){

        //if (!$request->ajax()) return redirect('/');

        $codofic = $request->codofic;
        $codresp = $request->codresp;
        $responsable = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
        ->where('resp.codresp','=', $codresp)
        ->where('resp.codofic','=', $codofic)
        ->select('resp.id','resp.nomresp','resp.cargo','oficina.nomofic','resp.api_estado','resp.codresp','resp.codofic')->first();
        return response()->json(['responsable' => $responsable]);
    }
    public function listarResponsable(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->where('articulos.stock','>','0')
            ->orderBy('articulos.id', 'desc')->paginate(10);
        }
        else{
            $articulos = Articulo::join('categorias','articulos.idcategoria','=','categorias.id')
            ->select('articulos.id','articulos.idcategoria','articulos.codigo','articulos.nombre','categorias.nombre as nombre_categoria','articulos.precio_venta','articulos.stock','articulos.descripcion','articulos.condicion')
            ->where('articulos.'.$criterio, 'like', '%'. $buscar . '%')
            ->where('articulos.stock','>','0')
            ->orderBy('articulos.id', 'desc')->paginate(10);
        }
        

        return ['articulos' => $articulos];
    }
    public function delete(Request $request){
        //echo (intval($request->codresp));
        $activo = Actual::where('codresp','=',$request->codresp)->where('codofic','=',$request->codofic)->get();
        $activo = $activo->count();
        if($activo==0){
            $res=Responsables::where('id',$request->id)->delete();
            return response()->json(['message' => 'Responsable Eliminado Exitosamente !!!']);
        }else{
            return response()->json(['message' => 'El Usuario tiene '.$activo.' Activos asignados, no se puede Eliminar!!!']);
        }
        
    }
    public function repResponsables(){
        $responsable = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
                                    ->select('resp.nomresp','resp.ci','oficina.nomofic','resp.cargo',
                                    'resp.observ',)->distinct('resp.id')->get();
        return response()->json(['responsable' => $responsable]);                      
    }
    public function listarporOficina(Request $request){
        $codofic = $request->codofic;
        $unidad = $request->unidad;
        $buscar = $request->buscar;
        $criterio = $request->criterio;
        if($buscar==''){
            $responsables = Responsables::where('resp.codofic','=',$codofic)->orwhere('unidad','=',$unidad)
            ->get();
             return [
                'responsables' => $responsables,
                'total'=>$responsables->count()
                ];
        }else{
            $responsables = Responsables::where('resp.codofic','=',$codofic)->orwhere('unidad','=',$unidad)
            ->where('resp.'.$criterio, 'like', '%'. $buscar . '%')
            ->get();
             return [
                'responsables' => $responsables,
                'total'=>$responsables->count()
                ];
        }
    }
    
}
