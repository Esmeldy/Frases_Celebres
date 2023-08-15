<?php

namespace App\Http\Controllers;

use App\Models\Autores;
use Illuminate\Http\Request;

class AutoresController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $autores = Autores::all();

        return response()->json($autores, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $nombre = trim($request->nombre); //Eliminar espacios
        $nombre = mb_strtolower($nombre, 'UTF-8'); //pasar a minúscula
        $nombre = ucfirst($nombre); //convertir la primera letra en mayúscula


        $apellidos = trim($request->apellidos);
        $apellidos = mb_strtolower($apellidos,'UTF-8');
        $apellidos = ucfirst($apellidos);

        $descripcion = trim($request->descripcion);

        $data = [
            'error' => 'Faltan campos por rellenar',

        ];
        if (empty($nombre)) {
            return response()->json($data, 404);
        }
        if (empty($apellidos)) {
            return response()->json($data, 404);
        }
        if (empty($descripcion)) {
            return response()->json($data, 404);
        }

        $autor = new Autores();
        $autor->nombre = $nombre;
        $autor->apellidos = $apellidos;
        $autor->descripcion = $descripcion;

        //Controlar que no exista en la BD
        $buscarAutor = Autores::where([
            'nombre' => $nombre,
            'apellidos' => $apellidos
        ])->get()->first();

        if (!empty($buscarAutor)) {
            $data = [
                'error' => 'El autor ya se encuentra en la base de datos',
                'autor' => $buscarAutor
            ];
            return response()->json($data, 200);
        } else {
            $autor->save();
        }

        if (!$autor->save()) {
            $data = [
                'error' => 'Algo ha ido mal',
            ];
            return response()->json($data, 404);
        }

        $data = [
            'message' => 'Autor creado correctamente',
            'autor' => $autor->toArray()
        ];
        return response()->json($data, 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Autores  $autores
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $autor = Autores::find($id);

        if (!$autor) {
            $data = [
                'message' => 'Autor no encontrado'
            ];
            return response()->json($data, 404);
        }
        $data = [
            'autor' => $autor
        ];
        return response()->json($data, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Autores  $autores
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $data = [];

        $autor = Autores::find($id);

        if (!$autor) {
            $data = [
                'message' => 'Autor no encontrado',
            ];
            return response()->json($data, 404);
        }
        if (!isset($request->nombre) || !isset($request->apellidos) || !isset($request->descripcion)) {
            $data = [
                'message' => 'Los campos no pueden estar vacíos',
            ];

            return response()->json($data, 404);
        }
        $nombre = trim($request->nombre);

        $apellidos = trim($request->apellidos);

        $descripcion = trim($request->descripcion);


        $autor->nombre = $nombre;
        $autor->apellidos = $apellidos;
        $autor->descripcion = $descripcion;

        $autor->save();
        $data = [
            'message' => 'Autor actualizado correctamente',
            'autor' => $autor
        ];

        return response()->json($data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Autores  $autores
     * @return \Illuminate\Http\Response
     */
    public function destroy($autorId)
    {
        $autor = Autores::find($autorId);

        if (!$autor) {
            $data = [
                'message' => 'Autor no encontrado',
            ];
            return response()->json($data, 404);
        }

        $autor->delete();
        $data = [
            'message' => 'Autor borrado correcramente',
            'autor' => $autor
        ];
        return response()->json($data, 200);
    }
}
