<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\SpecStatus;
use App\Http\Controllers\Controller;
use App\Models\ApiSpec;
use App\Services\ApiRuntime\ApiQueryService;
use App\Services\ApiRuntime\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DynamicApiController extends Controller
{
    public function __construct(
        protected ApiQueryService $queryService,
        protected ApiResponseTransformer $transformer,
    ) {}

    public function index(Request $request, string $slug): JsonResponse
    {
        $spec = $this->resolveSpec($slug);
        $this->ensureMethodAllowed($spec, 'GET');

        $result = $this->queryService->list($spec, $request);

        return response()->json(
            $this->transformer->transformCollection($spec, $result),
        );
    }

    public function show(Request $request, string $slug, string $id): JsonResponse
    {
        $spec = $this->resolveSpec($slug);
        $this->ensureMethodAllowed($spec, 'GET');

        $row = $this->queryService->find($spec, $id);

        if (! $row) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json(
            $this->transformer->transformItem($spec, $row),
        );
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $spec = $this->resolveSpec($slug);
        $this->ensureMethodAllowed($spec, 'POST');

        $data = $this->transformer->filterInputFields($spec, $request->all());
        $row = $this->queryService->create($spec, $data);

        return response()->json(
            $this->transformer->transformItem($spec, $row),
            201,
        );
    }

    public function update(Request $request, string $slug, string $id): JsonResponse
    {
        $spec = $this->resolveSpec($slug);
        $this->ensureMethodAllowed($spec, 'PUT');

        $data = $this->transformer->filterInputFields($spec, $request->all());
        $row = $this->queryService->update($spec, $id, $data);

        if (! $row) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json(
            $this->transformer->transformItem($spec, $row),
        );
    }

    public function destroy(Request $request, string $slug, string $id): JsonResponse
    {
        $spec = $this->resolveSpec($slug);
        $this->ensureMethodAllowed($spec, 'DELETE');

        $deleted = $this->queryService->delete($spec, $id);

        if (! $deleted) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json(null, 204);
    }

    protected function resolveSpec(string $slug): ApiSpec
    {
        $spec = ApiSpec::where('slug', $slug)
            ->where('status', SpecStatus::DEPLOYED)
            ->first();

        abort_if(! $spec, 404, 'API endpoint not found.');

        return $spec;
    }

    protected function ensureMethodAllowed(ApiSpec $spec, string $method): void
    {
        $config = $spec->configuration ?? [];
        $allowedMethods = $config['methods'] ?? ['GET', 'POST', 'PUT', 'DELETE'];

        abort_if(! in_array($method, $allowedMethods), 405, 'Method not allowed.');
    }
}
