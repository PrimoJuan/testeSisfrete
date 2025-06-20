<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model{
    protected $table = 'pagamentos';
    protected $primaryKey = 'idpagamento';  // <<< Definido explicitamente

    public $timestamps = false;
    protected $fillable = [
        'idPedido',
        'metodoPagamento',
        'valorPago',
        'dataPagamento',
        'dataCadastro',
    ];

    public function pedido(){
        return $this->belongsTo(Pedido::class, 'idpedido', 'idpedido');
    }
}
