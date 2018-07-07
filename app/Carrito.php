<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
	protected $table = 'carritos';
	protected $fillable = [
        'car_cantidad', 'car_precio', 'producto_id', 'user_id',
    ];
    function producto(){
        return $this->belongsTo('App\Producto');
    }
    function user(){
        return $this->belongsTo('App\Usuario');
    }
}
