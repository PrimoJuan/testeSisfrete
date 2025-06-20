<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clientes extends Model{
    protected $table = 'clientes';
    protected $primaryKey = 'idcliente';  // <<< Definido explicitamente

    public $timestamps = false;

    protected $fillable = [
        'nomeCliente',
        'dataCadastro',
        'dataAtualizacao',
    ];

    public function pedidos(){
        return $this->hasMany(Pedido::class, 'idcliente', 'idcliente');
    }
}
