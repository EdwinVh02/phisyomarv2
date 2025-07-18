<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEspecialidadRequest;
use App\Http\Requests\UpdateEspecialidadRequest;
use App\Models\Especialidad;

class EspecialidadController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return Especialidad::class;
    }

    protected function getStoreRequestClass(): ?string
    {
        return StoreEspecialidadRequest::class;
    }

    protected function getUpdateRequestClass(): ?string
    {
        return UpdateEspecialidadRequest::class;
    }
}
