<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produtos extends Model{
    protected $table = 'produtos';
    protected $primaryKey = 'idproduto';

    public $timestamps = false;
    protected $fillable = [
        'descricaoproduto',
        'precoproduto',
        'datacadastro',
        'dataatualizacao'
    ];

    public function categorias(){
        return $this->belongsToMany(Categoria::class, 'produtocategoria', 'idproduto', 'idcategoria');
    }
}
