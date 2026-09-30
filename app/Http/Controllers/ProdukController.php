<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdukRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        return view('kasir.produk.index', [
            'produk' => Product::cari(trim((string) $request->query('q')))
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'q' => trim((string) $request->query('q')),
        ]);
    }

    public function create(): View
    {
        return view('kasir.produk.form', ['produk' => new Product]);
    }

    public function store(ProdukRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return redirect()->route('kasir.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $produk): View
    {
        return view('kasir.produk.form', ['produk' => $produk]);
    }

    public function update(ProdukRequest $request, Product $produk): RedirectResponse
    {
        $produk->update($request->validated());

        return redirect()->route('kasir.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $produk): RedirectResponse
    {
        $produk->delete();

        return redirect()->route('kasir.produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
