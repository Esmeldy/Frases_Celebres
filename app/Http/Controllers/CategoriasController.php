<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use Illuminate\Http\Request;

class CategoriasController extends Controller
{


    /**
     * @OA\Get(
     *     path="/api/categorias",
     *     summary="Obtiene una lista de todas las categorías",
     *     tags={"Categorias"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorías obtenida correctamente",
     *         @OA\JsonContent()
     *         )
     *     )
     * )
     */
    public function index()
    {
        $categorias = Categorias::all();

        return response()->json($categorias, 200);
    }


    /**
     * @OA\Post(
     *     path="/api/categorias",
     *     summary="Crea una nueva categoría",
     *     tags={"Categorias"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="categoria",
     *                 type="string",
     *                 description="Nombre de la categoría a crear",
     *                 example="Literatura"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría creada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Categoría creada correctamente"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Error al crear la categoría",
     *         @OA\JsonContent()
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/categorias/{id}",
     *     summary="Obtiene una categoría por su ID",
     *     tags={"Categorias"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID de la categoría a obtener",
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría encontrada",
     *         @OA\JsonContent()
     *
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Categoría no encontrada"
     *             )
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/categorias/{id}",
     *     summary="Actualiza una categoría existente",
     *     tags={"Categorias"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID de la categoría a actualizar",
     *         example=1
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="categoria",
     *                 type="string",
     *                 description="Nuevo nombre de la categoría",
     *                 example="Novela"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría actualizada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Categoría actualizada correctamente"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada o el campo está vacío",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Categoría no encontrada"
     *             )
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/categorias/{id}",
     *     summary="Elimina una categoría existente",
     *     tags={"Categorias"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID de la categoría a eliminar",
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría borrada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Categoría borrada correctamente"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Categoría no encontrada"
     *             )
     *         )
     *     )
     * )
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
