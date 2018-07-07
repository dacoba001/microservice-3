<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockMoves extends Model
{
    protected $table = 'stock_moves';
	protected $fillable = [
        'id', 'tipo', 'cantidad_move', 'cantidad_stock', 'stock_id', 'detallepedidos_id'
    ];

    public function stock(){
    	return $this->belongsTo('App\Stock');
    }
    public function detallepedido(){
        return $this->belongsTo('App\Detallepedido', 'detallepedidos_id');
    }
    public function importacion(){
        return $this->hasOne('App\Importacion');
    }
}
