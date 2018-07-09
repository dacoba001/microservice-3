<?php

namespace App\Http\Controllers;

use App\Detallepedido;
use App\Carrito;
use App\Pedido;
use App\Stock;
use App\StockMoves;
use Illuminate\Http\Request;

use App\Http\Requests;

class PedidosController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('user', 'cliente')->get();
        return response()->json($pedidos, 200);
    }
    public function getEstado($estado)
    {
        $pedidos = Pedido::with('user', 'cliente')->where('ped_estado', $estado)->get();
        return response()->json($pedidos, 200);
    }
    public function reportePedido(Request $request)
    {
        $pedidos = Pedido::with(['user', 'cliente', 'productos', 'productos.producto'])
            ->where('ped_fecha_ini','>=', $request['start_date'])
            ->where('ped_fecha_ini','<=',$request['end_date'].' 23:59:59')
            ->orderBy('ped_fecha_ini', 'asc')->get();
        foreach ($pedidos as $pedido){
            $pedido['total_productos'] = $pedido->productos()->sum('ped_cantidad');
            foreach ($pedido->productos as $item){
                $pedido['total_precio'] += $item['ped_cantidad'] * $item['ped_precio'];
                $pedido['total_precio_recibido'] += ($item['ped_cantidad_entregado'] - $item['ped_cantidad_devuelto']) * $item['ped_precio'];
            };
        }
        return response()->json($pedidos, 200);
    }

    public function reporteCliente(Request $request)
    {
        $pedidos = Pedido::with(['cliente.user', 'productos'])
            ->where('ped_fecha_ini','>=', $request['start_date'])
            ->where('ped_fecha_ini','<=',$request['end_date'].' 23:59:59')
            ->orderBy('ped_fecha_ini', 'asc');

        $users = $pedidos->get()->pluck('cliente')->pluck('user')->unique();

        foreach ($users as $user)
        {
            $pedidos = Pedido::with(['cliente.user', 'productos'])
                ->where('ped_fecha_ini','>=', $request['start_date'])
                ->where('ped_fecha_ini','<=',$request['end_date'].' 23:59:59')
                ->orderBy('ped_fecha_ini', 'asc');
            $pedidos_user = $pedidos->where('user_id', $user->id)->get();
            $user['cant_pedidos'] = $pedidos_user->count();
            $user['cant_productos'] = $pedidos_user->pluck('productos')->collapse()->sum('ped_cantidad');
        }

        return response()->json($users, 200);
    }

    public function reporteStock(Request $request)
    {
        $stock_moves = StockMoves::with(['stock', 'stock.producto', 'detallepedido', 'importacion'])
            ->where('created_at','>=', $request['start_date'])
            ->where('created_at','<=',$request['end_date'].' 23:59:59')
            ->orderBy('created_at', 'asc');
        if(isset($request['stock_id']) and $request['stock_id'] != 0){
            $stock_moves->where('stock_id','=', $request['stock_id']);
        }
        $stock_moves = $stock_moves->get();
        return response()->json($stock_moves, 200);
    }

    public function getDetalle($pedido_id)
    {
        $detallepedido = Detallepedido::with(['producto', 'producto.tipo', 'producto.stocks'])->where('pedido_id', $pedido_id)->get();
        if($detallepedido)
        {
            return response()->json($detallepedido, 200);
        }
        return response()->json(["Pedido no encontrado"], 404);
    }
    public function getCliente($user_id)
    {
        $pedidos = Pedido::with('user', 'cliente')->where('user_id', $user_id)->get();
        if($pedidos)
        {
            return response()->json($pedidos, 200);
        }
        return response()->json(["Pedidos no encontrados"], 404);
    }
    public function getClientePendiente($user_id)
    {
        $pedidos = Pedido::with('user', 'cliente')->where('user_id', $user_id)->where('ped_estado', 'pendiente')->get();
        if($pedidos)
        {
            return response()->json($pedidos, 200);
        }
        return response()->json(["Pedidos no encontrados"], 404);
    }
    public function getClienteEntregado($user_id)
    {
        $pedidos = Pedido::with('user', 'cliente')->where('user_id', $user_id)->where('ped_estado', 'Entregado')->get();
        if($pedidos)
        {
            return response()->json($pedidos, 200);
        }
        return response()->json(["Pedidos no encontrados"], 404);
    }

    public function showPedido($id)
    {
        $pedido = Pedido::find($id);
        if($pedido)
        {
            return response()->json($pedido, 200);
        }
        return response()->json(["Pedido no encontrado"], 404);
    }

    public function createPedido(Request $request)
    {
        $pedido = Pedido::create([
            'ped_fecha_ini' => date('Y-m-d H:i:s'),
            'ped_estado' => 'pendiente',
            'cliente_id' => $request['cliente_id'],
            'user_id' => $request['user_id']
        ]);
        $carrito = Carrito::with(['producto', 'producto.tipo', 'producto.stocks'])->where('user_id', $request['user_id'])->get();
        foreach ($carrito as $detalle)
        {
            Detallepedido::create([
                'ped_cantidad' => $detalle['car_cantidad'],
                'ped_precio' => $detalle['car_precio'],
                'producto_id' => $detalle['producto_id'],
                'pedido_id' => $pedido['id'],
            ]);
            Carrito::destroy($detalle['id']);
        }
        return response()->json($pedido,201);
    }
    protected function PedidoEntregado($id)
    {
        $detallepedido = Detallepedido::where('pedido_id', $id)->get();
        if($detallepedido->sum('ped_cantidad') == $detallepedido->sum('ped_cantidad_entregado'))
        {
            return True;
        }
        return False;
    }
    public function pedidoValidar(Request $request, $pedido_id)
    {
        $detallepedido = Detallepedido::with(['producto', 'producto.tipo', 'producto.stocks'])->where('pedido_id', $pedido_id)->get();
        $response['status'] = "failed";
        foreach ($detallepedido as $detalle)
        {
            $stock_cant = $detalle['producto']['stocks']['stk_cantidad'];
            $restante_cant = $detalle['ped_cantidad'] - $detalle['ped_cantidad_entregado'];
            if($restante_cant > 0 and $stock_cant >= $restante_cant){
                $response['status'] = "success";
                Stock::where('id', $detalle['producto']['stocks']['id'])
                    ->update([
                        'stk_cantidad' => $stock_cant - $restante_cant,
                    ]);
                Detallepedido::where('id', $detalle['id'])
                    ->update([
                        'ped_cantidad_entregado' => $detalle['ped_cantidad']
                    ]);
                $response['message'][] = $restante_cant . " Productos entregados de '" . $detalle['producto']['pro_nombre'] . "'.";
            }
        }
        if($this->PedidoEntregado($pedido_id)){
            Pedido::where('id', $pedido_id)
                ->update([
                    'ped_estado' => 'Entregado',
                ]);
        }
        return response()->json($response,201);
    }
//    public function updateCarrito(Request $request, $id)
//    {
//        $carrito = Producto::where('id', $id)
//            ->update([
//                'pro_nombre' => $request['pro_nombre'],
//                'pro_descripcion' => $request['pro_descripcion'],
//                'pro_codigo' => $request['pro_codigo'],
//                'tipo_id' => $request['tipo_id'],
//            ]);
//        return response()->json($carrito,201);
//    }
//    public function destroyCarrito($id)
//    {
//        $carrito = Carrito::destroy($id);
//        return response()->json($carrito,201);
//    }

    public function getDetallepedido($id)
    {
        $detallepedido = Detallepedido::with(['producto', 'producto.stocks'])->where('id',$id)->first();
        if($detallepedido)
        {
            return response()->json($detallepedido, 200);
        }
        return response()->json(["Pedido no encontrado"], 404);
    }
    public function putEntregarDetallepedido(Request $request, $id)
    {
        $detallepedido = Detallepedido::with(['producto', 'producto.tipo', 'producto.stocks'])->where('id', $id)->first();
        $Restante = $detallepedido['ped_cantidad'] - $detallepedido['ped_cantidad_entregado'];
        if($request['proCantidadPrcesar'] <= $Restante){
            $detallepedidoResponse = Detallepedido::where('id', $id)
                ->update([
                    'ped_cantidad_entregado' => $detallepedido['ped_cantidad_entregado'] + $request['proCantidadPrcesar']
                ]);
            $cantidad_stock = $detallepedido['producto']['stocks']['stk_cantidad'] - $request['proCantidadPrcesar'];
            Stock::where('id', $detallepedido['producto']['stocks']['id'])
                ->update([
                    'stk_cantidad' => $cantidad_stock
                ]);
            StockMoves::create([
                'tipo' => 'Entrega',
                'cantidad_move' => $request['proCantidadPrcesar'],
                'cantidad_stock' => $cantidad_stock,
                'detallepedidos_id' => $detallepedido['id'],
                'stock_id' => $detallepedido['producto']['stocks']['id']
            ]);
            if($this->PedidoEntregado($detallepedido['pedido_id'])){
                Pedido::where('id', $detallepedido['pedido_id'])
                    ->update([
                        'ped_estado' => 'Entregado',
                    ]);
            }
        }
        return response()->json($detallepedidoResponse, 200);
    }
    public function putDevolverDetallepedido(Request $request, $id)
    {
        $detallepedido = Detallepedido::with(['producto', 'producto.tipo', 'producto.stocks'])->where('id', $id)->first();
        $reponerStock = $request['proReponerStock'] == 'on' ? true : false;
        $Posible = $detallepedido['ped_cantidad_entregado'] - $detallepedido['ped_cantidad_devuelto'];
        if($request['proCantidadPrcesar'] <= $Posible){
            $detallepedidoResponse = Detallepedido::where('id', $id)
                ->update([
                    'ped_cantidad_devuelto' => $detallepedido['ped_cantidad_devuelto'] + $request['proCantidadPrcesar']
                ]);
            if($reponerStock){
                Stock::where('id', $detallepedido['producto']['stocks']['id'])
                    ->update([
                        'stk_cantidad' => $detallepedido['producto']['stocks']['stk_cantidad'] + $request['proCantidadPrcesar']
                    ]);
            }
        }
        return response()->json($detallepedidoResponse, 200);
    }
}
