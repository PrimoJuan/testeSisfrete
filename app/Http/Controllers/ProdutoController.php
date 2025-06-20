<?php

namespace App\Http\Controllers;
use App\Produtos;
use Illuminate\Http\Request;

class ProdutoController extends Controller{
    public function index(Request $request) {
        $query = Produtos::with('categorias');

        if ($request->has('idCategoria')) {
            $idCategoria = $request->input('idCategoria');
            $query->whereHas('categorias', function ($q) use ($idCategoria) {
                $q->where('categorias.idcategoria', $idCategoria);
            });
        }

        if ($request->has('preco_min')) {
            $query->where('precoproduto', '>=', $request->input('preco_min'));
        }

        if ($request->has('preco_max')) {
            $query->where('precoproduto', '<=', $request->input('preco_max'));
        }

        $produtos = $query->get()->map(function ($produto) {
            return $produto->categorias->map(function ($categoria) use ($produto) {
                return [
                    'idproduto' => $produto->idproduto,
                    'descricaoproduto' => $produto->descricaoproduto,
                    'precoproduto' => $produto->precoproduto,
                    'idcategoria' => $categoria->idcategoria,
                    'descricaocategoria' => $categoria->descricaocategoria,
                ];
            });
        })->collapse()->values();

        return response()->json([
            'status' => 200,
            'quantidade' => $produtos->count(),
            'produtos' => $produtos
        ]);
    }
}
