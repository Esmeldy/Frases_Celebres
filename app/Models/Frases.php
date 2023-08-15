<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Frases extends Model
{
    use HasFactory;

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Autores::class);
    }

    /**
     * Relación 1 - n
     * una categoria puede pertenecer a muchas frases y una frase pertenece a una categoria
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categorias::class);
    }





}
