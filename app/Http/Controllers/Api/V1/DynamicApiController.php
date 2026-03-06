<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\SpecStatus;
use App\Http\Controllers\Controller;
use App\Models\ApiSpec;
use App\Models\ApiSpecTable;
use App\Services\ApiRuntime\ApiHeaderService;
use App\Services\ApiRuntime\ApiQueryService;
use App\Services\ApiRuntime\ApiRequestLogger;
use App\Services\ApiRuntime\ApiResponseTransformer;
use App\Services\ApiRuntime\ApiValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DynamicApiController extends Controller
{
    public function __construct(
        protected ApiQueryService $queryService,
        protected ApiResponseTransformer $transformer,
        protected ApiValidationService $validator,
        protected ApiHeaderService $headerService,
        protected ApiRequestLogger $logger,
    ) {}

    public function resources(Request $request, string $slug): JsonResponse
    {
        $startTime = microtime(true);
        $spec = $this->resolveSpec($slug);
        $request->attributes->set('api_spec', $spec);

        $tables = $spec->tables()->get();

        if ($tables->isEmpty()) {
            $result = $this->handleSingleTableList($spec, $request);

            return $this->respond($result, $spec, $request, $startTime);
        }

        $resources = $tables->map(fn (ApiSpecTable $table) => [
            'name' => $table->resource_name,
            'operations' => $table->operations ?? $table->getDefaultOperations(),
        ])->values()->all();

        $response = response()->json([
            'data' => $resources,
            'meta' => [
                'spec' => $spec->name,
                'slug' => $spec->slug,
                'total_resources' => count($resources),
            ],
        ]);

        $this->logger->log($request, $spec, $request->attributes->get('api_spec_key'), 200, $startTime);

        return $this->headerService->apply($response, $spec, $startTime);
    }

    public function index(Request $request, string $slug, string $resource): JsonResponse
    {
        $startTime = microtime(true);
        $spec = $this->resolveSpec($slug);
        $request->attributes->set('api_spec', $spec);
        $table = $this->resolveTable($spec, $resource);
        $this->ensureOperationAllowed($table, 'list');

        $result = $this->queryService->list($spec, $request, $table->table_name);
        $result = $this->transformer->transformCollection($spec, $result, $table);

        return $this->respond($result, $spec, $request, $startTime, $resource);
    }

    public function show(Request $request, string $slug, string $resource, string $id): JsonResponse
    {
        $startTime = microtime(true);
        $spec = $this->resolveSpec($slug);
        $request->attributes->set('api_spec', $spec);
        $table = $this->resolveTable($spec, $resource);
        $this->ensureOperationAllowed($table, 'show');

        $row = $this->queryService->find($spec, $id, $table->table_name);

        if (! $row) {
            return $this->respondError('Not found.', 404, $spec, $request, $startTime, $resource);
        }

        $result = $this->transformer->transformItem($spec, $row, $table);

        return $this->respond($result, $spec, $request, $startTime, $resource);
    }

    public function store(Request $request, string $slug, string $resource): JsonResponse
    {
        $startTime = microtime(true);
        $spec = $this->resolveSpec($slug);
        $request->attributes->set('api_spec', $spec);
        $table = $this->resolveTable($spec, $resource);
        $this->ensureOperationAllowed($table, 'create');

        try {
            $input = $this->transformer->filterInputFields($spec, $request->all(), $table);
            $validated = $this->validator->validate($spec, $input, $table);
            $row = $this->queryService->create($spec, $validated, $table->table_name);
            $result = $this->transformer->transformItem($spec, $row, $table);

            return $this->respond($result, $spec, $request, $startTime, $resource, 201);
        } catch (ValidationException $e) {
            return $this->respondValidationError($e, $spec, $request, $startTime, $resource);
        }
    }

    public function update(Request $request, string $slug, string $resource, string $id): JsonResponse
    {
        $startTime = microtime(true);
        $spec = $this->resolveSpec($slug);
        $request->attributes->set('api_spec', $spec);
        $table = $this->resolveTable($spec, $resource);
        $this->ensureOperationAllowed($table, 'update');

        try {
            $input = $this->transformer->filterInputFields($spec, $request->all(), $table);
            $validated = $this->validator->validate($spec, $input, $table, isUpdate: true);
            $row = $this->queryService->update($spec, $id, $validated, $table->table_name);

            if (! $row) {
                return $this->respondError('Not found.', 404, $spec, $request, $startTime, $resource);
            }

            $result = $this->transformer->transformItem($spec, $row, $table);

            return $this->respond($result, $spec, $request, $startTime, $resource);
        } catch (ValidationException $e) {
            return $this->respondValidationError($e, $spec, $request, $startTime, $resource);
        }
    }

    public function destroy(Request $request, string $slug, string $resource, string $id): JsonResponse
    {
        $startTime = microtime(true);
        $spec = $this->resolveSpec($slug);
        $request->attributes->set('api_spec', $spec);
        $table = $this->resolveTable($spec, $resource);
        $this->ensureOperationAllowed($table, 'delete');

        $deleted = $this->queryService->delete($spec, $id, $table->table_name);

        if (! $deleted) {
            return $this->respondError('Not found.', 404, $spec, $request, $startTime, $resource);
        }

        return $this->respond(null, $spec, $request, $startTime, $resource, 204);
    }

    protected function resolveSpec(string $slug): ApiSpec
    {
        $spec = ApiSpec::where('slug', $slug)
            ->where('status', SpecStatus::DEPLOYED)
            ->first();

        abort_if(! $spec, 404, 'API endpoint not found.');

        return $spec;
    }

    protected function resolveTable(ApiSpec $spec, string $resource): ApiSpecTable
    {
        $table = $spec->tables()->where('resource_name', $resource)->first();

        if ($table) {
            return $table;
        }

        // Fallback: single-table spec where resource matches selected_tables
        $selectedTables = $spec->selected_tables ?? [];

        if (count($selectedTables) === 1) {
            // Auto-create a virtual table mapping for legacy single-table specs
            $tableName = $selectedTables[0];
            $virtualTable = new ApiSpecTable;
            $virtualTable->api_spec_id = $spec->id;
            $virtualTable->table_name = $tableName;
            $virtualTable->resource_name = $resource;
            $virtualTable->operations = $spec->configuration['operations']
                ?? $virtualTable->getDefaultOperations();

            return $virtualTable;
        }

        abort(404, 'Resource not found.');
    }

    protected function ensureOperationAllowed(ApiSpecTable $table, string $operation): void
    {
        if (! $table->isOperationAllowed($operation)) {
            abort(405, 'This operation is not available for this resource.');
        }
    }

    protected function handleSingleTableList(ApiSpec $spec, Request $request): array
    {
        $this->ensureMethodAllowed($spec, 'GET');
        $result = $this->queryService->list($spec, $request);

        return $this->transformer->transformCollection($spec, $result);
    }

    protected function ensureMethodAllowed(ApiSpec $spec, string $method): void
    {
        $config = $spec->configuration ?? [];
        $allowedMethods = $config['methods'] ?? ['GET'];

        abort_if(! in_array($method, $allowedMethods), 405, 'Method not allowed.');
    }

    protected function respond(
        mixed $data,
        ApiSpec $spec,
        Request $request,
        float $startTime,
        ?string $resource = null,
        int $status = 200,
    ): JsonResponse {
        $response = response()->json($data, $status);

        if (is_array($data) && isset($data['meta'])) {
            $this->headerService->applyPaginationHeaders($response, $data['meta']);
        }

        $this->logger->log(
            $request, $spec, $request->attributes->get('api_spec_key'),
            $status, $startTime, $resource,
        );

        return $this->headerService->apply($response, $spec, $startTime);
    }

    protected function respondError(
        string $message,
        int $status,
        ApiSpec $spec,
        Request $request,
        float $startTime,
        ?string $resource = null,
    ): JsonResponse {
        $response = response()->json(['message' => $message], $status);

        $this->logger->log(
            $request, $spec, $request->attributes->get('api_spec_key'),
            $status, $startTime, $resource,
        );

        return $this->headerService->apply($response, $spec, $startTime);
    }

    protected function respondValidationError(
        ValidationException $e,
        ApiSpec $spec,
        Request $request,
        float $startTime,
        ?string $resource = null,
    ): JsonResponse {
        $response = response()->json([
            'message' => 'Validation failed.',
            'errors' => $e->errors(),
        ], 422);

        $this->logger->log(
            $request, $spec, $request->attributes->get('api_spec_key'),
            422, $startTime, $resource,
        );

        return $this->headerService->apply($response, $spec, $startTime);
    }
}
