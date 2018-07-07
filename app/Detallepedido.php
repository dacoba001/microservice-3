<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Detallepedido extends Model
{
	protected $table = 'detallepedidos';
	protected $fillable = [
        'ped_cantidad', 'ped_precio', 'producto_id', 'pedido_id',
    ];
    function producto(){
        return $this->belongsTo('App\Producto');
    }
    function pedido(){
        return $this->belongsTo('App\Pedido');
    }
    function stock_move(){
        return $this->hasOne('App\StockMoves');
    }
}
