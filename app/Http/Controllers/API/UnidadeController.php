<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Unidade;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{
    // Listar todas as unidades
    public function index()
    {
        return response()->json(
            Unidade::orderBy('id')->get()
        );
    }

    // Cadastrar uma unidade
    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'empresa' => 'required|string|max:255',
        ]);

        $unidade = Unidade::create($dados);

        return response()->json($unidade, 201);
    }

    // Consultar uma unidade
    public function show(Unidade $unidade)
    {
        return response()->json($unidade);
    }

    // Atualizar uma unidade
    public function update(Request $request, Unidade $unidade)
    {
        $dados = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'empresa' => 'sometimes|required|string|max:255',
        ]);

        $unidade->update($dados);

        return response()->json($unidade);
    }

    // Excluir uma unidade
    public function destroy(Unidade $unidade)
    {
        $unidade->delete();

        return response()->json(null, 204);
    }
}
