<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminInventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->when($request->string('search')->trim()->value(), function ($query, string $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('low_stock_only'), fn ($query) => $query->whereRaw('stock <= low_stock_threshold'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.inventory.index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product): View
    {
        $history = $this->inventory->getHistory($product, 100);

        return view('admin.inventory.show', [
            'product' => $product,
            'history' => $history,
        ]);
    }

    public function adjust(Product $product): View
    {
        return view('admin.inventory.adjust', [
            'product' => $product,
        ]);
    }

    public function storeAdjustment(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'new_stock' => ['required', 'integer', 'min:0', 'max:9999999'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($product, $data): void {
            $this->inventory->recordAdjustment(
                $product,
                $data['new_stock'],
                $data['note'],
                Auth::id()
            );
        });

        return redirect()
            ->route('admin.inventory.show', $product)
            ->with('success', "Stok {$product->name} berhasil disesuaikan ke {$data['new_stock']} unit.");
    }

    public function exportHistory(Product $product)
    {
        $history = InventoryMovement::query()
            ->where('product_id', $product->id)
            ->with(['user', 'product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=inventory_'.$product->id.'_'.now()->format('YmdHis').'.csv',
        ];

        $callback = function () use ($history, $product) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Produk:', $product->name]);
            fputcsv($file, ['Tanggal Export:', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'Tanggal',
                'Tipe',
                'Perubahan Stok',
                'Stok Sebelum',
                'Stok Sesudah',
                'Pengguna',
                'Catatan',
            ]);

            foreach ($history as $movement) {
                fputcsv($file, [
                    $movement->created_at->format('Y-m-d H:i:s'),
                    ucfirst($movement->type),
                    $movement->quantity_delta,
                    $movement->stock_before,
                    $movement->stock_after,
                    $movement->user->name,
                    $movement->note ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
