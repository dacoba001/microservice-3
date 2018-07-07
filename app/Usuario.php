<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model{

// protected $table = 'usuarios';
// protected $primaryKey = 'id';
// protected $fillable = ['id','nombre','appaterno','apmaterno','tipo_cuenta','telefono','nacimiento'];
// protected $hidden = ['created_at','updated_at'];
    protected $table = 'users';
    protected $fillable = [
        'nombre', 'nombredeusuario', 'appaterno', 'apmaterno', 'fecha_nac', 'telefono', 'email', 'password', 'tipo_cuenta',
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];
}