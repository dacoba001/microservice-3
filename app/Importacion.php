<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Importacion extends Model
{
    protected $table = 'importacions';
    protected $fillable = [
        'imp_fecha', 'imp_cantidad', 'imp_estado', 'producto_id' , 'stock_moves_id'
    ];
    function producto(){
        return $this->belongsTo('App\Producto');
    }
    function stock_move(){
        return $this->belongsTo('App\StockMoves');
    }
}
