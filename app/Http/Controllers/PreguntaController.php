<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreguntaRequest;
use App\Http\Requests\UpdatePreguntaRequest;
use App\Models\Pregunta;

class PreguntaController extends BaseResourceController
{
    /**
     * Get the model class for this controller
     */
    protected function getModelClass(): string
    {
        return Pregunta::class;
    }

    /**
     * Get the store request class
     */
    protected function getStoreRequestClass(): ?string
    {
        return StorePreguntaRequest::class;
    }

    /**
     * Get the update request class
     */
    protected function getUpdateRequestClass(): ?string
    {
        return UpdatePreguntaRequest::class;
    }
}
