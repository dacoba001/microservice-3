<?php

namespace App\Http\Controllers;

use App\Importacion;
use Illuminate\Http\Request;
use App\Stock;
use App\StockMoves;

use App\Http\Requests;

class ImportacionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $importaciones = Importacion::with(['producto', 'producto.tipo', 'producto.stocks'])->orderBy('imp_estado', 'desc')->get();
        return response()->json($importaciones, 200);
    }
    public function reporteImportacion(Request $request)
    {
        $importaciones = Importacion::with(['producto', 'producto.tipo', 'producto.stocks'])
            ->where('imp_fecha','>=', $request['start_date'])
            ->where('imp_fecha','<=',$request['end_date'].' 23:59:59')
            ->orderBy('imp_fecha', 'asc')->get();
        return response()->json($importaciones, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createImportacion(Request $request)
    {
        $importacion = Importacion::create([
            'imp_fecha' =>  date('Y-m-d H:i:s'),
            'imp_cantidad' => $request['imp_cantidad'],
            'imp_estado' => 'Peticion',
            'producto_id' => $request['producto_id'],
        ]);
//        Stock::where('producto_id', $request['producto_id'])->increment('stk_cantidad', $request['imp_cantidad']);
        return response()->json($importacion,201);
    }
    public function updateImportacion(Request $request, $id)
    {
//        $importacion = Importacion::with(['producto', 'producto.tipo', 'producto.stocks'])->where('id','=', $id)->get()[0];
        $importacion = Importacion::with(['producto', 'producto.tipo', 'producto.stocks'])->where('id', $id)->first();
        $new_estado="";
        switch ($importacion['imp_estado']){
            case "Peticion":
                $new_estado = "Embarcado";
                break;
            case "Embarcado":
                $new_estado = "En Frontera";
                break;
            case "En Frontera":
                $new_estado = "Canal Aduanero";
                break;
            case "Canal Aduanero":
                $new_estado = "Aduana Nacional";
                break;
            case "Aduana Nacional":
                $cantidad_stock = $importacion['producto']['stocks']['stk_cantidad'] + $importacion['imp_cantidad'];
                Stock::where('id', $importacion['producto']['stocks']['id'])
                    ->update([
                        'stk_cantidad' => $cantidad_stock,
                    ]);
                $stock_move = StockMoves::create([
                    'tipo' => 'Importacion',
                    'cantidad_move' => $importacion['imp_cantidad'],
                    'cantidad_stock' => $cantidad_stock,
                    'stock_id' => $importacion['producto']['stocks']['id']
                ]);
                Importacion::where('id', $id)
                    ->update([
                        'stock_moves_id' => $stock_move['id']
                    ]);
                $new_estado = "Importado";
                break;
            default:
                break;
        }
        $importacion = Importacion::where('id', $id)
            ->update([
                'imp_estado' => $new_estado,
            ]);
        return response()->json($importacion,201);

    }
    public function destroyImportacion($id)
    {
        $importacion = Importacion::destroy($id);
        return response()->json($importacion,201);
    }
    /**qqqq
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
