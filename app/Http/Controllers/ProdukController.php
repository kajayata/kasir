<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdukRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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
        $data = $request->validated();

        $file = $request->file('image');
        if ($file) {
            $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = time().'_'.preg_replace('/[^A-Za-z0-9\-_]/', '_', $original).'.'.$ext;
            $folder = 'products';
            $path = $folder.'/'.$filename;

            $manager = new ImageManager(Driver::class);
            $image = $manager->read($file->getRealPath())->cover(800, 800);

            if ($ext === 'png') {
                $encoded = $image->encode()->toPng();
            } else {
                $encoded = $image->encode()->toJpeg(quality: 85);
            }

            Storage::disk('public')->put($path, (string) $encoded);
            $data['image'] = $path;
        }

        Product::create($data);

        return redirect()->route('kasir.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $produk): View
    {
        return view('kasir.produk.form', ['produk' => $produk]);
    }

    public function update(ProdukRequest $request, Product $produk): RedirectResponse
    {
        $data = $request->validated();

        $file = $request->file('image');
        if ($file) {
            if ($produk->image && Storage::disk('public')->exists($produk->image)) {
                Storage::disk('public')->delete($produk->image);
            }

            $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = time().'_'.preg_replace('/[^A-Za-z0-9\-_]/', '_', $original).'.'.$ext;
            $folder = 'products';
            $path = $folder.'/'.$filename;

            $manager = new ImageManager(Driver::class);
            $image = $manager->read($file->getRealPath())->cover(800, 800);

            if ($ext === 'png') {
                $encoded = $image->encode()->toPng();
            } else {
                $encoded = $image->encode()->toJpeg(quality: 85);
            }

            Storage::disk('public')->put($path, (string) $encoded);
            $data['image'] = $path;
        }

        $produk->update($data);

        return redirect()->route('kasir.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $produk): RedirectResponse
    {
        $produk->delete();

        return redirect()->route('kasir.produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
