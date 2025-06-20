<?php

namespace App\Http\Controllers;
use App\Produtos;
use App\Pedidos;
use App\Clientes;
use App\PedidoProduto;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PedidoController extends Controller{

	public function store(Request $request){

		$clienteId = $request->input('id_cliente');
		$itens = $request->input('itens');
		$metodoPagamento = $request->input('metodo_pagamento');
		 
		if(empty($clienteId)){
				return response()->json([
											'status' => 400,
											'mensagem' => 'Dados obrigatórios não informados',
											'detalhes' => 'O Campo id_cliente é obrigatório'
										], 400);
		}

		if (!is_array($itens) || count($itens) == 0){
			return response()->json([
										'status' => 400,
										'mensagem' => 'Dados obrigatórios não informados',
										'detalhes' => 'É necessário ter pelo menos 1 item para cadastrar um pedido'
									], 400);
		}

		if (empty($metodoPagamento)) {
			return response()->json([
				'status' => 400,
				'mensagem' => 'Dados obrigatórios não informados',
				'detalhes' => 'Informe um valor entre 1 e 3 para o campo metodo_pagamento'
			], 400);
		}

        $cliente = Clientes::find($clienteId);

		if(!$cliente){
			return response()->json([
										'status' => 404,
										'mensagem' => 'Cliente Não Encontrado',
										'detalhes' => 'Verifique se o id_cliente informado está correto'
									], 400);
		}

       $idsProdutos = array_column($itens, 'produto');

       $qtdePorProduto = array_count_values($idsProdutos);

		foreach($qtdePorProduto as $idProduto => $quantidade){
			if ($quantidade > 1){
				return response()->json([
					'status' => 400,
					'mensagem' => 'Dados inválidos',
					'detalhes' => 'Produto '.$idProduto.' está duplicado no pedido'
				], 400);
			}
		}
       
		$totalPedido = 0;
		$itensValidos = [];
        foreach($itens as $item) {
			$idProduto = $item['produto'] ?? null;
			$quantidade = $item['quantidade'] ?? 0;
            
            if($quantidade <= 0){
             return response()->json([
                                        'status' => 400,
                                        'mensagem' => 'Dados inválidos',
                                        'detalhes' => 'Quantidade do produto '.$idProduto.' inválida'
                                    ], 400);
            }

            $produto = Produtos::find($idProduto);

            if (!$produto){
				return response()->json([
											'status' => 400,
											'mensagem' => 'Dados inválidos',
											'detalhes' => 'Produto '.$idProduto.' Não Encontrado'
										], 400);
            }

            $itensValidos[] =	[
									'produto_id' => $produto->idproduto,
									'descricao_produto' => $produto->descricaoproduto,
									'quantidade' => $quantidade,
									'preco_unitario' => $produto->precoproduto
								];

			$totalPedido += $quantidade * $produto->precoproduto;
        }

		switch ($metodoPagamento) {
			case 1:
				$descricaoPagamento = 'Cartão de Crédito';
				break;
			case 2:
				$descricaoPagamento = 'Boleto Bancário';
				break;
			case 3:
				$descricaoPagamento = 'Pix';
				break;
		}

        DB::beginTransaction();

        try {
            $pedido = Pedidos::create	([
											'idcliente' => $cliente->idcliente,
											'datacadastro' => date('Y-m-d H:i:s')
										]);

            foreach ($itensValidos as $item) {
				PedidoProduto::create	([
											'idpedido' => $pedido->idpedido,
											'idproduto' => $item['produto_id'],
											'quantidade' => $item['quantidade'],
											'precounitario' => $item['preco_unitario']
										]);
            }

			DB::table('pagamentos')->insert([
												'idpedido' => $pedido->idpedido,
												'metodopagamento' => $descricaoPagamento,
												'valorpago' => $totalPedido,
												'datapagamento' => date('Y-m-d H:i:s'),
												'datacadastro' => date('Y-m-d H:i:s')
											]);

		DB::commit();

		return response()->json([
									'status' => 201,
									'mensagem' => 'Pedido criado com sucesso',
									'pedido' => [
										'id' => $pedido->idpedido,
										'cliente_nome' => $cliente->nomecliente,
										'data' =>  $pedido->datacadastro,
										'itens' => $itensValidos,
										'metodo_pagamento' => $descricaoPagamento,
										'valor_total' => $totalPedido
									]
								], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
										'status' => 500,
										'mensagem' => 'Erro ao criar o pedido.',
										'detalhes' => $e->getMessage()
									], 500);
        }
    }

    public function update(Request $request, $idPedido){
		$clienteId = $request->input('id_cliente');
		$itens = $request->input('itens');
		$metodoPagamento = $request->input('metodo_pagamento');

		if(empty($clienteId)){
			return response()->json([
										'status' => 400,
										'mensagem' => 'Dados obrigatórios não informados',
										'detalhes' => 'O Campo id_cliente é obrigatório'
									], 400);
		}

		if(empty($metodoPagamento)){
			return response()->json([
										'status' => 400,
										'mensagem' => 'Dados obrigatórios não informados',
										'detalhes' => 'Informe um valor entre 1 e 3 para o campo metodo_pagamento'
									], 400);
		}

		$pedido = Pedidos::find($idPedido);
		if(!$pedido){
			return response()->json([
										'status' => 404,
										'mensagem' => 'Pedido não encontrado',
										'detalhes' => 'Verifique se o id do pedido informado está correto'
									], 404);
		}

		$cliente = Clientes::find($clienteId);
		if(!$cliente){
			return response()->json([
										'status' => 404,
										'mensagem' => 'Cliente Não Encontrado',
										'detalhes' => 'Verifique se o id_cliente informado está correto'
									], 404);
		}

		$idsProdutos = array_column($itens, 'produto');
		$qtdePorProduto = array_count_values($idsProdutos);

		foreach($qtdePorProduto as $idProduto => $quantidade){
			if ($quantidade > 1) {
				return response()->json([
											'status' => 400,
											'mensagem' => 'Dados inválidos',
											'detalhes' => 'Produto ' . $idProduto . ' está duplicado no pedido'
										], 400);
			}
		}

		$totalPedido = 0;
		$itensValidos = [];
		foreach ($itens as $item) {
			$idProduto = $item['produto'] ?? null;
			$quantidade = $item['quantidade'] ?? 0;

			if ($quantidade <= 0) {
				return response()->json([
											'status' => 400,
											'mensagem' => 'Dados inválidos',
											'detalhes' => 'Quantidade do produto ' . $idProduto . ' inválida'
										], 400);
			}

			$produto = Produtos::find($idProduto);
			if (!$produto) {
				return response()->json([
					'status' => 400,
					'mensagem' => 'Dados inválidos',
					'detalhes' => 'Produto ' . $idProduto . ' Não Encontrado'
				], 400);
			}

			$itensValidos[] = 	[
									'produto_id' => $produto->idproduto,
									'descricao_produto' => $produto->descricaoproduto,
									'quantidade' => $quantidade,
									'preco_unitario' => $produto->precoproduto
								];

			$totalPedido += $quantidade * $produto->precoproduto;
		}

		switch ($metodoPagamento) {
			case 1:
				$descricaoPagamento = 'Cartão de Crédito';
				break;
			case 2:
				$descricaoPagamento = 'Boleto Bancário';
				break;
			case 3:
				$descricaoPagamento = 'Pix';
				break;
		}

		DB::beginTransaction();

		try{			
			$pedido->update([
				'idcliente' => $cliente->idcliente,
				'datacadastro' => date('Y-m-d H:i:s')
			]);

			if($itensValidos == [] || empty($itensValidos)){
				PedidoProduto::where('idpedido', $pedido->idpedido)->delete();
			}else{
				$itensAtuais = PedidoProduto::where('idpedido', $pedido->idpedido)->get()->keyBy('idproduto');
				$itensNovos = collect($itensValidos)->keyBy('produto_id');

				foreach ($itensAtuais as $idProduto => $itemAtual) {
					if (!$itensNovos->has($idProduto)) {
						$itemAtual->delete();
					}
				}

				foreach ($itensNovos as $idProduto => $itemNovo) {
					if ($itensAtuais->has($idProduto)) {
						$itemAtual = $itensAtuais->get($idProduto);

						if($itemAtual->quantidade != $itemNovo['quantidade']){
							$itemAtual->update([
													'quantidade' => $itemNovo['quantidade'],
													'precounitario' => $itemNovo['preco_unitario'],
												]);
						}
					}else{
						PedidoProduto::create	([
													'idpedido' => $pedido->idpedido,
													'idproduto' => $idProduto,
													'quantidade' => $itemNovo['quantidade'],
													'precounitario' => $itemNovo['preco_unitario'],
												]);
					}
				}

				DB::table('pagamentos')->where('idpedido', $pedido->idpedido)->update	([
																							'metodopagamento' => $descricaoPagamento,
																							'valorpago' => $totalPedido,
																							'datapagamento' => date('Y-m-d H:i:s'),
																						]);
			}

			DB::commit();

			return response()->json([
										'status' => 200,
										'mensagem' => 'Pedido atualizado com sucesso',
										'pedido' => [
											'id' => $pedido->idpedido,
											'cliente_nome' => $cliente->nomecliente,
											'data' => $pedido->datacadastro,
											'itens' => $itensValidos,
											'metodo_pagamento' => $descricaoPagamento,
											'valor_total' => $totalPedido
										]
									], 200);
		}catch(\Exception $e){
			DB::rollBack();
			return response()->json([
										'status' => 500,
										'mensagem' => 'Erro ao atualizar o pedido.',
										'detalhes' => $e->getMessage()
									], 500);
		}
	}

}
