<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Produtos;
use App\Clientes;
use App\Pagamento;

class Pedidos extends Model{
    protected $table = 'pedidos';
    protected $primaryKey = 'idpedido';

    public $timestamps = false;

    protected $fillable = [
        'idpedido',
        'idcliente',
        'datacadastro',
        'dataatualizacao',
    ];

    public function cliente(){
        return $this->belongsTo(Clientes::class, 'idcliente', 'idcliente');
    }

    public function produtos(){
        return $this->belongsToMany(Produtos::class, 'pedidoProduto', 'idpedido', 'idproduto')->withPivot('quantidade', 'precounitario');
    }

    public function pagamentos(){
        return $this->hasMany(Pagamento::class, 'idpedido', 'idpedido');
    }
}
