<?php

namespace App\Http\Controllers;

use App\Models\Padecimiento;
use App\Http\Requests\StorePadecimientoRequest;
use App\Http\Requests\UpdatePadecimientoRequest;

class PadecimientoController extends BaseResourceController
{
    /**
     * Get the model class for this controller
     */
    protected function getModelClass(): string
    {
        return Padecimiento::class;
    }

    /**
     * Get the store request class
     */
    protected function getStoreRequestClass(): ?string
    {
        return StorePadecimientoRequest::class;
    }

    /**
     * Get the update request class
     */
    protected function getUpdateRequestClass(): ?string
    {
        return UpdatePadecimientoRequest::class;
    }
}
