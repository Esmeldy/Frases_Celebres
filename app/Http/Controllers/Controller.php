<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;


    /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="API Frases Célebres",
     *      description="API sobre frases célebres y motivadoras en castellano. Contiene categoria y autores. Creado en Laravel",
     *      @OA\Contact(
     *          email="esmeldyfm@gmail.com"
     *      ),
     *      @OA\License(
     *          name="Apache 2.0",
     *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
     *      )
     * )
     * @OA\Schemes(format="http")
     *
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     in="header",
     *     name="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT"
     * )
     *
     * @OA\Tag(
     *     name="Acceso",
     *     description="Endpoints de acceso"
     * )
     * @OA\Tag(
     *     name="Frases",
     *     description="Endpoints de frases"
     * )
     * @OA\Tag(
     *     name="Autores",
     *     description="Endpoints de autores"
     * )
     * @OA\Tag(
     *     name="Categorias",
     *     description="Endpoints de categorias"
     * )
     *
     */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
