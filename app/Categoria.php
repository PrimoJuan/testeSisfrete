<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model{
    protected $table = 'categorias';
    protected $primaryKey = 'idCategoria';
    public $timestamps = false;

     public function produtos(){
        return $this->belongsToMany(
            Produto::class,
            'produtoCategoria',
            'idCategoria',
            'idProduto'
        );
    }
}
