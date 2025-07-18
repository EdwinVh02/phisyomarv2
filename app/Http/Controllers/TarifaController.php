<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTarifaRequest;
use App\Http\Requests\UpdateTarifaRequest;
use App\Models\Tarifa;

class TarifaController extends BaseResourceController
{
    /**
     * Get the model class for this controller
     */
    protected function getModelClass(): string
    {
        return Tarifa::class;
    }

    /**
     * Get the store request class
     */
    protected function getStoreRequestClass(): ?string
    {
        return StoreTarifaRequest::class;
    }

    /**
     * Get the update request class
     */
    protected function getUpdateRequestClass(): ?string
    {
        return UpdateTarifaRequest::class;
    }
}
