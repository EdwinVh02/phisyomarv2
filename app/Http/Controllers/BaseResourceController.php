<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

abstract class BaseResourceController extends Controller
{
    protected $model;
    protected $storeRequest;
    protected $updateRequest;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get the model class for this controller
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the store request class (optional)
     */
    protected function getStoreRequestClass(): ?string
    {
        return null;
    }

    /**
     * Get the update request class (optional)
     */
    protected function getUpdateRequestClass(): ?string
    {
        return null;
    }

    /**
     * Get the model instance
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = app($this->getModelClass());
        }
        return $this->model;
    }

    /**
     * Display a listing of the resource with optional pagination.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 20);
        $model = $this->getModel();
        
        if ($perPage === 'all') {
            return response()->json($model->all(), 200);
        }
        
        return response()->json($model->paginate($perPage), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->getValidatedData($request, 'store');
        $resource = $this->getModel()->create($data);
        return response()->json($resource, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $resource = $this->getModel()->findOrFail($id);
        return response()->json($resource, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $resource = $this->getModel()->findOrFail($id);
        $data = $this->getValidatedData($request, 'update');
        $resource->update($data);
        return response()->json($resource, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $resource = $this->getModel()->findOrFail($id);
        $resource->delete();
        return response()->json(['message' => 'Eliminado correctamente'], 200);
    }

    /**
     * Get validated data from request
     */
    protected function getValidatedData(Request $request, string $operation)
    {
        if ($operation === 'store' && $this->getStoreRequestClass()) {
            $requestClass = $this->getStoreRequestClass();
            $formRequest = app($requestClass);
            $formRequest->setContainer(app());
            $formRequest->setRedirector(app('redirect'));
            return $formRequest->validated();
        }

        if ($operation === 'update' && $this->getUpdateRequestClass()) {
            $requestClass = $this->getUpdateRequestClass();
            $formRequest = app($requestClass);
            $formRequest->setContainer(app());
            $formRequest->setRedirector(app('redirect'));
            return $formRequest->validated();
        }

        // Fallback to basic validation or override in child class
        return $this->getDefaultValidationRules($request, $operation);
    }

    /**
     * Override this method in child classes for custom validation
     */
    protected function getDefaultValidationRules(Request $request, string $operation): array
    {
        return $request->all(); // Override in child classes
    }
}