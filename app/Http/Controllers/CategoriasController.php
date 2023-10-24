<?php

namespace App\Http\Controllers;

use App\Models\Autores;
use App\Models\Categorias;
use Illuminate\Http\Request;

class CategoriasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorias = Categorias::all();

        return response()->json($categorias, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (empty($request->categoria)) {
            $data = [
                'message' => 'El campo no puede estar vacío',
            ];
            return response()->json($data, 404);
        }

        $nombreCategoria = trim($request->categoria); //Eliminar espacios en los extremos
        $nombreCategoria = mb_strtolower($nombreCategoria, 'UTF-8'); //pasar a minúscula

        //comprobarsi existe categoria
        $findCategoria = Categorias::where('categoria', $nombreCategoria)->get()->toArray();
        if ($findCategoria) {
            $data = [
                'message' => 'Esta categoria ya existe',
                'categoria' => $findCategoria,
            ];
            return response()->json($data, 404);
        }

        $categoria = new Categorias();
        $categoria->categoria = $nombreCategoria;
        $categoria->save();

        $data = [
            'message' => 'Categoria creada correctamente',
            'categoria' => $categoria,
        ];
        return response()->json($data, 200);
    }

    /**
     *
     * Display the specified resource.
     *
     * @param  \App\Models\Categorias  $categorias
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $categoria = Categorias::find($id);

        if (!$categoria) {
            $data = [
                'message' => 'Categoria no encontrada'
            ];
            return response()->json($data, 404);
        }
        $data = [
            'categoria' => $categoria
        ];
        return response()->json($data, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Categorias  $categorias
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = [];

        $categoria = Categorias::find($id);

        if (!$categoria) {
            $data = [
                'message' => 'Categoria no encontrada',
            ];
            return response()->json($data, 404);
        }
        if (!isset($request->categoria)) {
            $data = [
                'message' => 'El campo no puede estar vacío',
            ];

            return response()->json($data, 404);
        }

        $nombreCategoria = trim($request->categoria);


        $categoria->categoria = $nombreCategoria;
        $categoria->save();

        $data = [
            'message' => 'Categoria actualizada correctamente',
            'categoria' => $categoria
        ];

        return response()->json($data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Categorias  $categorias
     * @return \Illuminate\Http\Response
     */
    public function destroy($idCategoria)
    {
        $categoria = Categorias::find($idCategoria);

        if (!$categoria) {
            $data = [
                'message' => 'Categoria no encontrada',
            ];
            return response()->json($data, 404);
        }

        $categoria->delete();
        $data = [
            'message' => 'Categoria borrada correcramente',
            'categoria' => $categoria
        ];
        return response()->json($data, 200);
    }
}

//Faltaría añadir más autores y frases de dichos autores

// yyyy despues añadir seguridad
