<?php

namespace App\Http\Controllers;

use App\Models\Autores;
use App\Models\Categorias;
use App\Models\Frases;
use Illuminate\Http\Request;

class FrasesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $frases = Frases::all();

        $data = $this->getConvertedArrayFrases($frases);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     "frase"=> "required|string",
        //     ""=> "",
        // ]);

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
            return response()->json('La frase ya se encuentra en la base de datos', 404);
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
     * Display the specified resource.
     *
     * @param  \App\Models\Frases  $frases
     * @return \Illuminate\Http\Response
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Frases  $frases
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Frases $frase)
    {
        $data = [];

        if (!isset($request->frase) || !isset($request->autor_id) || !isset($request->categoria_id)) {
            $data = [
                'message' => 'Los campos no pueden estar vacíos',
            ];

            return response()->json($data, 404);
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Frases  $frases
     * @return \Illuminate\Http\Response
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
     * devuelve una frase aleatoria cada vez que se ejecute
     * @return \Illuminate\Http\Response
     */
    public function getRandomFrases()
    {

        $frases = Frases::all();
        $data = $this->getConvertedArrayFrases($frases);

        $randomNum = random_int(0, count($frases) - 1);

        return response()->json($data[$randomNum]);
    }

    /**
     * Devuelve las frases correcpondientes con el autor recibido
     * tanto por id como por nombre del autor
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
