<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoriaProduto extends Model{
    protected $table = 'categorias';
    protected $primaryKey = 'idcategoria';

    public $timestamps = false;
    protected $fillable = [
        'descricaocategoria',
        'datacadastro',
        'dataatualizacao',
    ];

    public function produtos(){
        return $this->belongsToMany(Produto::class, 'produtocategoria', 'idcategoria', 'idproduto');
    }
}
