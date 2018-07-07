<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    protected $fillable = [
        'ped_fecha_ini', 'ped_fecha_fin', 'ped_estado', 'cliente_id', 'user_id',
    ];
    function user(){
        return $this->belongsTo('App\Usuario');
    }
    function cliente(){
        return $this->belongsTo('App\Cliente');
    }
    public function productos(){
        return $this->hasMany('App\Detallepedido');
    }
}
