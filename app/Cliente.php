<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
	protected $table = 'clientes';
	//protected $primaryKey = 'id';
	protected $fillable = [
        'cli_nombre', 'cli_direccion', 'cli_zona', 'user_id',
    ];
    function user(){
        return $this->belongsTo('App\Usuario');
    }
}
