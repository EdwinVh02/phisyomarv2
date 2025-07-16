<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Http\Requests\StoreEncuestaRequest;
use App\Http\Requests\UpdateEncuestaRequest;

class EncuestaController extends BaseResourceController
{
    /**
     * Get the model class for this controller
     */
    protected function getModelClass(): string
    {
        return Encuesta::class;
    }

    /**
     * Get the store request class
     */
    protected function getStoreRequestClass(): ?string
    {
        return StoreEncuestaRequest::class;
    }

    /**
     * Get the update request class
     */
    protected function getUpdateRequestClass(): ?string
    {
        return UpdateEncuestaRequest::class;
    }
}
