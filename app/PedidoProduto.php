<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoProduto extends Model{
    protected $table = 'pedidoProduto';
    
    protected $primaryKey = 'idPedidoProduto';
    public $timestamps = false;

    protected $fillable = [
        'idpedidoproduto',
        'idpedido',
        'idproduto',
        'quantidade',
        'precounitario',
    ];

    public function pedido(){
        return $this->belongsTo(Pedido::class, 'idpedido', 'idpedido');
    }

    public function produto(){
        return $this->belongsTo(Produto::class, 'idproduto', 'idproduto');
    }
}
