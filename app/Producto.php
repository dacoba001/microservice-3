<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
	protected $fillable = [
        'pro_nombre', 'pro_descripcion', 'pro_codigo', 'tipo_id',
    ];
	function tipo(){
    	return $this->belongsTo('App\Tipo');
    }
    function stocks(){
        return $this->hasOne('App\Stock');
    }
    function carritos(){
        return $this->hasOne('App\Carrito');
    }
}