<?php

namespace App\Http\Controllers;

use App\Models\Autores;
use App\Models\Categorias;
use App\Models\Frases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrasesController extends Controller
{


    /**
     * @OA\Get(
     *     path="/api/frases",
     *     summary="Obtiene una lista de frases con sus autores y categorías",
     *     tags={"Frases"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de frases obtenida correctamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="ID de la frase",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="frase",
     *                     type="string",
     *                     description="Contenido de la frase",
     *                     example="El amor en los tiempos del cólera."
     *                 ),
     *                 @OA\Property(
     *                     property="autor",
     *                     type="string",
     *                     description="Nombre completo del autor",
     *                     example="Gabriel García Márquez"
     *                 ),
     *                 @OA\Property(
     *                     property="categoria",
     *                     type="string",
     *                     description="Nombre de la categoría",
     *                     example="Novela"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron frases",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="No se encontraron frases"
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {


        $frases = Frases::
              join('autores', 'autores.id','=','frases.autor_id')
            ->join('categorias','categorias.id','=','frases.categoria_id')
            ->select('frases.id','frase',DB::raw('CONCAT(autores.nombre, \' \', autores.apellidos) as autor'), 'categorias.categoria')
            ->orderby('frases.id')
            ->get();


        return response()->json($frases);
    }


    /**
     * @OA\Post(
     *     path="/api/frases",
     *     summary="Crea una nueva frase",
     *     tags={"Frases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="frase",
     *                 type="string",
     *                 description="El contenido de la frase",
     *                 example="El amor en los tiempos del cólera."
     *             ),
     *             @OA\Property(
     *                 property="autor",
     *                 type="string",
     *                 description="Nombre del autor de la frase",
     *                 example="Gabriel García Márquez"
     *             ),
     *             @OA\Property(
     *                 property="categoria",
     *                 type="string",
     *                 description="Categoría de la frase",
     *                 example="Novela"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Frase creada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 description="ID de la frase",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="frase",
     *                 type="string",
     *                 description="El contenido de la frase",
     *                 example="El amor en los tiempos del cólera."
     *             ),
     *             @OA\Property(
     *                 property="autor_id",
     *                 type="integer",
     *                 description="ID del autor asociado",
     *                 example=2
     *             ),
     *             @OA\Property(
     *                 property="categoria_id",
     *                 type="integer",
     *                 description="ID de la categoría asociada",
     *                 example=3
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Error en la creación de la frase",
     *         @OA\JsonContent(
     *             type="string",
     *             example="No ha escrito ninguna frase"
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="La frase ya existe en la base de datos",
     *         @OA\JsonContent(
     *             type="string",
     *             example="La frase ya se encuentra en la base de datos"
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {

        //Objeto a guardar
        $frase = new Frases();


        //Autor
        $autor = explode(' ', $request->autor, 2);
        if (empty($request->autor)) {
            $frase->autor_id = null;
        }

        if (empty($request->categoria)) {
            $frase->categoria_id = null;
        }

        if (count($autor) > 1) {
            $nombreAutor = $autor[0];
        } else {
            $nombreAutor = $request->autor;
        }

        if (!is_null($nombreAutor)){
            $idAutorBuscado = Autores::where('nombre','LIKE', '%'.$nombreAutor.'%')->first();
            if ($idAutorBuscado) {
                $idAutor = $idAutorBuscado->id;
            }else {
                $idAutor = 2; // 2 - Desconocido
            }

        }


        //Categoria
        $tipoCategoria = $request->categoria;
        $idCategoria = -1;

        $categoria = Categorias::where('categoria', 'LIKE', '%'.$tipoCategoria.'%')->first();

        if (!$categoria) {
            $idCategoria = Categorias::where('categoria', 'otro')->first()->id;
        } else {
            $idCategoria = $categoria->id;
        }

        //Guardando los datos
        $frase->frase = trim($request->frase);
        $frase->categoria_id = $idCategoria;
        $frase->autor_id = $idAutor;

        if (empty($frase->frase)) {
            return response()->json('No ha escrito ninguna frase', 404);
        }

        //Controlar que la frase no esté añadida en la BD
        if ($this->quoteIsAdded($frase->frase)) {
            return response()->json('La frase ya se encuentra en la base de datos', 409);
        }

        $frase->save();
        return response()->json($frase);

    }

    /**
     * Método que devuelve si la frase existe o no
     * en la BD
     */
    public function quoteIsAdded($frase): bool
    {
        $encontrado = Frases::where('frase', $frase)->first();

        return $encontrado ? true : false;
    }



    /**
     * @OA\Get(
     *     path="/api/frases/{id}",
     *     summary="Obtiene una frase específica por su ID",
     *     tags={"Frases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID de la frase a obtener",
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Frase obtenida correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 description="ID de la frase",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="frase",
     *                 type="string",
     *                 description="Contenido de la frase",
     *                 example="El amor en los tiempos del cólera."
     *             ),
     *             @OA\Property(
     *                 property="autor",
     *                 type="string",
     *                 description="Nombre completo del autor",
     *                 example="Gabriel García Márquez"
     *             ),
     *             @OA\Property(
     *                 property="categoria",
     *                 type="string",
     *                 description="Nombre de la categoría",
     *                 example="Novela"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Frase no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Frase no encontrada"
     *             )
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $data = [];
        $frase = Frases::find($id);

        if (empty($frase)) {
            $data = [
                'message' => 'Frase no encontrada',
            ];
            return response()->json($data, 404);
        }
        $data = [
            'id' => $frase->id,
            'frase' => $frase->frase,
            'autor' => $frase->autor->nombre . '' . $frase->autor->apellidos,
            'categoria' => $frase->categoria->categoria,
        ];

        return response()->json($data);

    }




    /**
     * @OA\Put(
     *     path="/api/frases/{id}",
     *     summary="Actualiza una frase existente",
     *     tags={"Frases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID de la frase a actualizar",
     *         example=1
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="frase",
     *                 type="string",
     *                 description="Contenido actualizado de la frase",
     *                 example="El amor es eterno mientras dura."
     *             ),
     *             @OA\Property(
     *                 property="autor_id",
     *                 type="integer",
     *                 description="ID del autor actualizado",
     *                 example=2
     *             ),
     *             @OA\Property(
     *                 property="categoria_id",
     *                 type="integer",
     *                 description="ID de la categoría actualizada",
     *                 example=3
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Frase actualizada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Frase actualizada correctamente"
     *             ),
     *             @OA\Property(
     *                 property="frase")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Los campos no pueden estar vacíos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Los campos no pueden estar vacíos"
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, Frases $frase)
    {
        $data = [];

        if (!isset($request->frase) || !isset($request->autor_id) || !isset($request->categoria_id)) {
            $data = [
                'message' => 'Los campos no pueden estar vacíos',
            ];

            return response()->json($data, 400);
        }

        $frase->frase = trim($request->frase);
        $frase->autor_id = trim($request->autor_id);
        $frase->categoria_id = trim($request->categoria_id);

        $frase->save();

        $data = [
            'message' => 'Frase actualizada correctamente',
            'frase' => $frase
        ];

        return response()->json($data, 200);

    }


    /**
     * @OA\Delete(
     *     path="/api/frases/{id}",
     *     summary="Elimina una frase existente",
     *     tags={"Frases"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID de la frase a eliminar",
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Frase borrada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Frase borrada correctamente"
     *             ),
     *             @OA\Property(
     *                 property="frase")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Frase no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No se ha podido eliminar la frase porque no se encuentra en la base de datos."
     *             )
     *         )
     *     )
     * )
     */
    public function destroy($idFrase)
    {
        $frase = Frases::find($idFrase);

        if (!$frase) {
            return response()->json([
                'message' => 'No se ha podido eliminar la frase porque no se encuentra en la base de datos.'
            ],404);
        }
        $frase->delete();
        $data = [
            'message' => 'Frase borrada correctamente',
            'frase' => $frase
        ];
        return response()->json($data);
    }


    /**
     * @OA\Get(
     *     path="/api/frases/random",
     *     summary="Obtiene una frase aleatoria",
     *     tags={"Frases"},
     *     @OA\Response(
     *         response=200,
     *         description="Frase aleatoria obtenida correctamente",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron frases en la base de datos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No se encontraron frases en la base de datos."
     *             )
     *         )
     *     )
     * )
     */
    public function getRandomFrases()
    {

        $frases = Frases::all();
        $data = $this->getConvertedArrayFrases($frases);

        $randomNum = random_int(0, count($frases) - 1);

        return response()->json($data[$randomNum]);
    }



    /**
     * @OA\Get(
     *     path="/api/frases/autor/{autor}",
     *     summary="Obtiene frases por autor",
     *     tags={"Frases"},
     *     @OA\Parameter(
     *         name="autor",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         ),
     *         description="Puede ser el ID del autor o su nombre completo",
     *         example="Gabriel García Márquez"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Frases obtenidas correctamente",
     *         @OA\JsonContent()
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Frases del autor no encontradas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Frases del autor no encontradas"
     *             )
     *         )
     *     )
     * )
     */
    public function getFrasesByAutor($autor){
        //Puede Ser nombre o ID ???
        $frases = null;
      if (is_numeric($autor)) {
         $frases = Frases::where('autor_id', $autor)->get();
         $data = $this->getConvertedArrayFrases($frases);

         return response()->json($data);

      }else {
        $autor = trim($autor);
        $autor = explode(' ',$autor,2);

            if (count($autor) != 2){
                //Solo recibido el nombre
                $autorNombre = $autor[0];
                $autorEncontrado = Autores::where('nombre', 'like',$autorNombre)
                    ->first();

            }else {
                //Recibido nombre y apellido
                $autorNombre = $autor[0];
                $autorApellido = $autor[1];
                $autorEncontrado = Autores::where('nombre', 'like',$autorNombre)
                    ->orWhere('apellidos', 'like', $autorApellido)
                    ->first();

            }
        //Buscar Frases del Autor

        if (empty($autorEncontrado)) {
            $data = [
                'message' => 'Frases del autor no encontradas',
            ];
            return response()->json($data, 404);
        }

        $frases = Frases::where('autor_id', $autorEncontrado->id)->get();
        $data = $this->getConvertedArrayFrases($frases);
        return response()->json($data);

      }

    }

    /**
     * Función que recibe una lista de frases y las tranforma en un array con
     * los IDs convertidos en nombres, no en números.
     */
    private function getConvertedArrayFrases($frases) : array{
        $arrayFrase = [];

        foreach ($frases as $frase) {
            $arrayFrase[] = [
                'id' => $frase->id,
                'frase' => $frase->frase,
                'autor' => $frase->autor->nombre . ' ' . $frase->autor->apellidos,
                'categoria' => $frase->categoria->categoria,
            ];
        }

        return $arrayFrase;
    }
}
