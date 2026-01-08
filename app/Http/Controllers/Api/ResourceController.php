<?php

namespace App\Http\Controllers\Api;

use App\Enum\CategoryType;
use App\Enum\Role;
use App\Http\Controllers\Api\Concerns\PaginatesApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BannerResource;
use App\Http\Resources\Api\BlogDetailResource;
use App\Http\Resources\Api\BlogResource;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\EventResource;
use App\Http\Resources\Api\FileProductBillResource;
use App\Http\Resources\Api\FileProductResource;
use App\Http\Resources\Api\GenericResource;
use App\Http\Resources\Api\LocationResource;
use App\Http\Resources\Api\PartnerBillDetailResource;
use App\Http\Resources\Api\PartnerBillResource;
use App\Http\Resources\Api\PartnerCategoryResource;
use App\Http\Resources\Api\PartnerMediaResource;
use App\Http\Resources\Api\PartnerProfileResource;
use App\Http\Resources\Api\PartnerServiceResource;
use App\Http\Resources\Api\RentProductResource;
use App\Http\Resources\Api\ReportResource;
use App\Http\Resources\Api\StatisticalResource;
use App\Http\Resources\Api\TagResource;
use App\Http\Resources\Api\ThreadResource;
use App\Http\Resources\Api\UserResource;
use App\Http\Resources\Api\VoucherResource;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DesignCategory;
use App\Models\Event;
use App\Models\EventOrganizationGuide;
use App\Models\FailedJob;
use App\Models\FileProduct;
use App\Models\FileProductBill;
use App\Models\GoodLocation;
use App\Models\Location;
use App\Models\Partner;
use App\Models\PartnerBill;
use App\Models\PartnerBillDetail;
use App\Models\PartnerCategory;
use App\Models\PartnerMedia;
use App\Models\PartnerProfile;
use App\Models\PartnerService;
use App\Models\RentalCategory;
use App\Models\RentProduct;
use App\Models\Report;
use App\Models\Statistical;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\Thread;
use App\Models\User;
use App\Models\VocationalKnowledge;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ResourceController extends Controller
{
    use PaginatesApi;

    private const DEFAULT_PER_PAGE = 20;
    private const MAX_PER_PAGE = 50;

    /**
     * @var array<string, array{model: class-string<Model>, resource: class-string, detail_resource?: class-string, scope?: \Closure}>
     */
    private array $resourceMap = [];

    public function __construct()
    {
        $this->resourceMap = [
            'banners' => [
                'model' => Banner::class,
                'resource' => BannerResource::class,
            ],
            'blogs' => [
                'model' => Blog::class,
                'resource' => BlogResource::class,
                'detail_resource' => BlogDetailResource::class,
            ],
            'blog-categories' => [
                'model' => BlogCategory::class,
                'resource' => CategoryResource::class,
                'scope' => fn (Builder $query) => $query->where('type', CategoryType::GOOD_LOCATION->value)
                    ->orWhere('type', CategoryType::EVENT_ORGANIZATION_GUIDE->value)
                    ->orWhere('type', CategoryType::VOCATIONAL_KNOWLEDGE->value),
            ],
            'categories' => [
                'model' => Category::class,
                'resource' => CategoryResource::class,
            ],
            'customers' => [
                'model' => Customer::class,
                'resource' => UserResource::class,
                'scope' => fn (Builder $query) => $query->role(Role::CLIENT->value),
            ],
            'design-categories' => [
                'model' => DesignCategory::class,
                'resource' => CategoryResource::class,
                'scope' => fn (Builder $query) => $query->where('type', CategoryType::DESIGN->value),
            ],
            'events' => [
                'model' => Event::class,
                'resource' => EventResource::class,
            ],
            'event-organization-guides' => [
                'model' => EventOrganizationGuide::class,
                'resource' => BlogResource::class,
                'detail_resource' => BlogDetailResource::class,
                'scope' => fn (Builder $query) => $query->where('type', CategoryType::EVENT_ORGANIZATION_GUIDE->value),
            ],
            'failed-jobs' => [
                'model' => FailedJob::class,
                'resource' => GenericResource::class,
            ],
            'file-products' => [
                'model' => FileProduct::class,
                'resource' => FileProductResource::class,
            ],
            'file-product-bills' => [
                'model' => FileProductBill::class,
                'resource' => FileProductBillResource::class,
            ],
            'good-locations' => [
                'model' => GoodLocation::class,
                'resource' => BlogResource::class,
                'detail_resource' => BlogDetailResource::class,
                'scope' => fn (Builder $query) => $query->where('type', CategoryType::GOOD_LOCATION->value),
            ],
            'locations' => [
                'model' => Location::class,
                'resource' => LocationResource::class,
            ],
            'partners' => [
                'model' => Partner::class,
                'resource' => UserResource::class,
                'scope' => fn (Builder $query) => $query->role(Role::PARTNER->value),
            ],
            'partner-bills' => [
                'model' => PartnerBill::class,
                'resource' => PartnerBillResource::class,
            ],
            'partner-bill-details' => [
                'model' => PartnerBillDetail::class,
                'resource' => PartnerBillDetailResource::class,
            ],
            'partner-categories' => [
                'model' => PartnerCategory::class,
                'resource' => PartnerCategoryResource::class,
            ],
            'partner-media' => [
                'model' => PartnerMedia::class,
                'resource' => PartnerMediaResource::class,
            ],
            'partner-profiles' => [
                'model' => PartnerProfile::class,
                'resource' => PartnerProfileResource::class,
            ],
            'partner-services' => [
                'model' => PartnerService::class,
                'resource' => PartnerServiceResource::class,
            ],
            'rental-categories' => [
                'model' => RentalCategory::class,
                'resource' => CategoryResource::class,
                'scope' => fn (Builder $query) => $query->where('type', CategoryType::RENTAL->value),
            ],
            'rent-products' => [
                'model' => RentProduct::class,
                'resource' => RentProductResource::class,
            ],
            'reports' => [
                'model' => Report::class,
                'resource' => ReportResource::class,
            ],
            'statistics' => [
                'model' => Statistical::class,
                'resource' => StatisticalResource::class,
            ],
            'tags' => [
                'model' => Tag::class,
                'resource' => TagResource::class,
            ],
            'taggables' => [
                'model' => Taggable::class,
                'resource' => GenericResource::class,
            ],
            'threads' => [
                'model' => Thread::class,
                'resource' => ThreadResource::class,
            ],
            'users' => [
                'model' => User::class,
                'resource' => UserResource::class,
                'scope' => fn (Builder $query) => $query->with('partnerProfile'),
            ],
            'vocational-knowledges' => [
                'model' => VocationalKnowledge::class,
                'resource' => BlogResource::class,
                'detail_resource' => BlogDetailResource::class,
                'scope' => fn (Builder $query) => $query->where('type', CategoryType::VOCATIONAL_KNOWLEDGE->value),
            ],
            'vouchers' => [
                'model' => Voucher::class,
                'resource' => VoucherResource::class,
            ],
        ];
    }

    public function index(Request $request, string $resource)
    {
        $config = $this->resolveResource($resource);
        $modelClass = $config['model'];

        $query = $this->applyScope($config, $modelClass::query());

        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'items' => $this->paginatedData($paginator, $config['resource']),
        ]);
    }

    public function show(Request $request, string $resource, int $id)
    {
        $config = $this->resolveResource($resource);
        $modelClass = $config['model'];

        $query = $this->applyScope($config, $modelClass::query());
        $model = $query->whereKey($id)->firstOrFail();

        $resourceClass = $config['detail_resource'] ?? $config['resource'];

        return response()->json([
            'item' => new $resourceClass($model),
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $config = $this->resolveResource($resource);
        $modelClass = $config['model'];

        /** @var Model $model */
        $model = new $modelClass();
        $data = $this->filterFillable($request->all(), $model);

        if (empty($data) && count($model->getFillable()) > 0) {
            return response()->json([
                'message' => 'No fillable attributes provided.',
            ], 422);
        }

        $model->fill($data);
        $model->save();

        return response()->json([
            'item' => new $config['resource']($model),
        ], 201);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $config = $this->resolveResource($resource);
        $modelClass = $config['model'];

        $query = $this->applyScope($config, $modelClass::query());
        $model = $query->whereKey($id)->firstOrFail();

        $data = $this->filterFillable($request->all(), $model);
        if (empty($data) && count($model->getFillable()) > 0) {
            return response()->json([
                'message' => 'No fillable attributes provided.',
            ], 422);
        }

        $model->fill($data);
        $model->save();

        return response()->json([
            'item' => new $config['resource']($model),
        ]);
    }

    public function destroy(Request $request, string $resource, int $id)
    {
        $config = $this->resolveResource($resource);
        $modelClass = $config['model'];

        $query = $this->applyScope($config, $modelClass::query());
        $model = $query->whereKey($id)->firstOrFail();
        $model->delete();

        return response()->json(['success' => true]);
    }

    private function resolveResource(string $resource): array
    {
        if (!array_key_exists($resource, $this->resourceMap)) {
            abort(404, 'Resource not found.');
        }

        return $this->resourceMap[$resource];
    }

    private function applyScope(array $config, Builder $query): Builder
    {
        if (isset($config['scope']) && is_callable($config['scope'])) {
            return ($config['scope'])($query);
        }

        return $query;
    }

    private function resolvePerPage(Request $request, int $default): int
    {
        $perPage = (int) $request->query('per_page', $default);
        $perPage = max(1, $perPage);

        return min(self::MAX_PER_PAGE, $perPage);
    }

    /**
     * @return array<string, mixed>
     */
    private function filterFillable(array $input, Model $model): array
    {
        $fillable = $model->getFillable();
        if (empty($fillable)) {
            return [];
        }

        return Arr::only($input, $fillable);
    }
}
