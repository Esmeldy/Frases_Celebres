<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Autores extends Model
{
    use HasFactory;

   /**
    * Relación 1 - n
    * un Autor puede tener muchas frases y una frase pertenece a un solo autor
    */
    public function frases(): HasMany {
        return $this->hasMany(Frases::class);
    }
}
