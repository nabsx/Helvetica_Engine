<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->when($request->string('search')->trim()->value(), function ($query, string $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->string('stock_status')->value(), function ($query, string $status): void {
                match ($status) {
                    'low' => $query->whereColumn('stock', '<=', 'low_stock_threshold'),
                    'ok' => $query->whereColumn('stock', '>', 'low_stock_threshold'),
                    default => null,
                };
            })
            ->when(true, function ($query) use ($request): void {
                match ($request->string('sort')->value()) {
                    'price_desc' => $query->orderByDesc('price'),
                    'price_asc' => $query->orderBy('price'),
                    'margin_desc' => $query->orderByRaw('(price - cost_price) DESC'),
                    'stock_asc' => $query->orderBy('stock'),
                    default => $query->orderBy('name'),
                };
            })
            ->paginate(10)
            ->withQueryString();

        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('stock')
            ->get(['id', 'name', 'stock', 'low_stock_threshold']);

        $marginBearingCount = Product::where('price', '>', 0)->whereNotNull('cost_price')->count();
        $avgMarginPercent = $marginBearingCount > 0
            ? (int) round(
                Product::where('price', '>', 0)->whereNotNull('cost_price')
                    ->selectRaw('AVG((price - cost_price) / price * 100) as avg_margin')
                    ->value('avg_margin') ?? 0
            )
            : null;

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::withCount('products')->orderBy('name')->get(),
            'activeCategoryCount' => Category::where('is_active', true)->count(),
            'lowStockProducts' => $lowStockProducts,
            'avgMarginPercent' => $avgMarginPercent,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(['stock' => 0, 'low_stock_threshold' => 5, 'is_available' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, InventoryService $inventory): RedirectResponse
    {
        $product = Product::create($this->validatedData($request, isNew: true));

        // Give the opening stock count a ledger entry too, so a product's
        // history never has an unexplained gap at the very start.
        $inventory->recordInitial($product, Auth::id());

        return redirect()->route('admin.products.index')->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'movements' => $product->inventoryMovements()->with('user')->latest('created_at')->limit(20)->get(),
        ]);
    }

    /**
     * Updates everything about a product EXCEPT stock. Stock is
     * intentionally excluded here — see adjustStock() — so there is no path
     * where `products.stock` changes without a matching InventoryMovement
     * row being written.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedData($request, isNew: false);

        if ($request->hasFile('image') && $product->image && str_starts_with($product->image, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    /**
     * The only route allowed to change a product's stock after creation —
     * e.g. after a physical stock count (opname) or correcting a mistake.
     * Always goes through InventoryService so the ledger and the cached
     * `products.stock` column can never drift apart.
     */
    public function adjustStock(Request $request, Product $product, InventoryService $inventory): RedirectResponse
    {
        $data = $request->validate([
            'new_stock' => ['required', 'integer', 'min:0'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($product, $data, $inventory): void {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $inventory->recordAdjustment($locked, $data['new_stock'], $data['note'], Auth::id());
        });

        return back()->with('success', "Stok {$product->name} berhasil disesuaikan.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'Produk sudah dipakai dalam transaksi dan tidak dapat dihapus. Nonaktifkan produk saja.');
        }

        if ($product->image && str_starts_with($product->image, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
        }

        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    private function validatedData(Request $request, bool $isNew): array
    {
        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0', 'lte:price'],
            'tax_name' => ['required', 'string', 'max:80'],
            'tax_code' => ['required', 'string', 'max:40'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_included' => ['nullable', 'boolean'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        // 'stock' is only settable at creation time (the opening count).
        // After that it can only move through adjustStock()/recordSale()/
        // recordRefund(), never through this general-purpose form.
        if ($isNew) {
            $rules['stock'] = ['required', 'integer', 'min:0'];
        }

        $data = $request->validate($rules);

        $data['is_available'] = $request->boolean('is_available');
        $data['tax_included'] = $request->boolean('tax_included');

        if ($request->hasFile('image')) {
            $data['image'] = '/storage/'.$request->file('image')->store('products', 'public');
        }

        return $data;
    }
}