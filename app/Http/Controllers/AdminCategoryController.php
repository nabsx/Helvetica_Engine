<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->string('sort')->value() ?: 'custom';

        $categories = match ($sort) {
            'name' => Category::withCount('products')->orderBy('name')->get(),
            'count' => Category::withCount('products')->get()->sortByDesc('products_count')->values(),
            default => Category::withCount('products')->ordered()->get(),
        };

        // Best-selling category by units sold, so the "Kategori Terlaris"
        // badge/stat reflects real sales instead of a guess.
        $topCategoryId = OrderItem::query()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('products.category_id as category_id, SUM(order_items.quantity) as total_qty')
            ->groupBy('products.category_id')
            ->orderByDesc('total_qty')
            ->value('category_id');

        return view('admin.categories.index', [
            'categories' => $categories,
            'sort' => $sort,
            'topCategoryId' => $topCategoryId,
            'stats' => [
                'total' => $categories->count(),
                'active' => $categories->where('is_active', true)->count(),
                'inactive' => $categories->where('is_active', false)->count(),
                'productsTotal' => $categories->sum('products_count'),
                'topCategoryName' => $topCategoryId ? optional($categories->firstWhere('id', $topCategoryId))->name : null,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:categories,name'],
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:categories,name,'.$category->id],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori masih memiliki produk. Pindahkan produknya terlebih dahulu.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Persists a new POS tab order. Called via fetch() from the drag-and-drop
     * list on the index page, so it only ever returns JSON.
     */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'exists:categories,id'],
        ]);

        DB::transaction(function () use ($data): void {
            foreach ($data['order'] as $index => $categoryId) {
                Category::whereKey($categoryId)->update(['position' => $index]);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    /**
     * Flips whether a category (and everything in it) shows up on the
     * cashier POS screen. Supports both the instant fetch() toggle and a
     * plain form fallback if JS is unavailable.
     */
    public function toggleActive(Request $request, Category $category): RedirectResponse|JsonResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'is_active' => $category->is_active]);
        }

        return back()->with('success', "Kategori {$category->name} berhasil diperbarui.");
    }
}