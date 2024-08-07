<?php

namespace App\Http\Controllers;

use App\Models\Autores;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AutoresController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/autores",
     *     summary="Obtiene una lista de autores",
     *     tags={"Autores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de autores",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solicitud incorrecta"
     *     )
     * )
     */
    public function index()
    {
        $autores = Autores::all();

        return response()->json($autores, 200);
    }


    /**
     * @OA\Post(
     *     path="/api/autores",
     *     summary="Crea un nuevo autor",
     *     tags={"Autores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="nombre",
     *                 type="string",
     *                 description="Nombre del autor",
     *                 example="Gabriel"
     *             ),
     *             @OA\Property(
     *                 property="apellidos",
     *                 type="string",
     *                 description="Apellidos del autor",
     *                 example="García Márquez"
     *             ),
     *             @OA\Property(
     *                 property="descripcion",
     *                 type="string",
     *                 description="Descripción del autor",
     *                 example="Escritor colombiano, autor de 'Cien años de soledad'."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Respuesta exitosa",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         example="Autor creado correctamente"
     *                     ),
     *                     @OA\Property(
     *                         property="autor",
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(
     *                         property="error",
     *                         type="string",
     *                         example="El autor ya se encuentra en la base de datos"
     *                     ),
     *                     @OA\Property(
     *                         property="autor")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Faltan campos por rellenar o algo ha ido mal",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Faltan campos por rellenar"
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $nombre = $this->sanitateText($request, "nombre");

        $apellidos = $this->sanitateText($request, "apellidos");

        $descripcion = $this->sanitateText($request, "descripcion");

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
     * @OA\Get(
     *     path="/api/autores/{id}",
     *     summary="Muestra un autor específico",
     *     tags={"Autores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID del autor"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del autor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="autor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Autor no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Autor no encontrado"
     *             )
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/autores/{id}",
     *     summary="Actualiza un autor existente",
     *     tags={"Autores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID del autor"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="nombre",
     *                 type="string",
     *                 description="Nombre del autor",
     *                 example="Gabriel"
     *             ),
     *             @OA\Property(
     *                 property="apellidos",
     *                 type="string",
     *                 description="Apellidos del autor",
     *                 example="García Márquez"
     *             ),
     *             @OA\Property(
     *                 property="descripcion",
     *                 type="string",
     *                 description="Descripción del autor",
     *                 example="Escritor colombiano, autor de 'Cien años de soledad'."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Autor actualizado correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Autor actualizado correctamente"
     *             ),
     *             @OA\Property(
     *                 property="autor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Autor no encontrado o campos vacíos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Autor no encontrado"
     *             )
     *         )
     *     )
     * )
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
        $nombre = $this->sanitateText($request, "nombre");
        $apellidos = $this->sanitateText($request, "apellidos");
        $descripcion = $this->sanitateText($request, "descripcion");

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
     * @OA\Delete(
     *     path="/api/autores/{id}",
     *     summary="Elimina un autor existente",
     *     tags={"Autores"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID del autor"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Autor borrado correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Autor borrado correctamente"
     *             ),
     *             @OA\Property(
     *                 property="autor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Autor no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Autor no encontrado"
     *             )
     *         )
     *     )
     * )
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

    private function sanitateText(Request $request, $field) {
        $fieldSanitated = trim($request->$field); //Eliminar espacios
        $fieldSanitated = mb_strtolower($fieldSanitated, 'UTF-8'); //pasar a minúscula
        $fieldSanitated = ucfirst($fieldSanitated); //convertir la primera letra en mayúscula

        return $fieldSanitated;
    }
}
