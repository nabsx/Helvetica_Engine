<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(['stock' => 0, 'low_stock_threshold' => 5, 'is_available' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = Product::create($this->validatedData($request));

        return redirect()->route('admin.products.index')->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image') && $product->image && str_starts_with($product->image, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', "Produk {$product->name} berhasil diperbarui.");
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

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0', 'lte:price'],
            'tax_name' => ['required', 'string', 'max:80'],
            'tax_code' => ['required', 'string', 'max:40'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_included' => ['nullable', 'boolean'],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data['is_available'] = $request->boolean('is_available');
        $data['tax_included'] = $request->boolean('tax_included');

        if ($request->hasFile('image')) {
            $data['image'] = '/storage/'.$request->file('image')->store('products', 'public');
        }

        return $data;
    }
}
