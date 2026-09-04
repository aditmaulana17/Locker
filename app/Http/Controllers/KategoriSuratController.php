<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use Illuminate\Http\Request;

class KategoriSuratController extends Controller
{
    /**
     * Tampilkan daftar kategori surat.
     */
    public function index(Request $request)
    {
        $kategoris = KategoriSurat::withCount(['suratMasuk', 'suratKeluar'])
            ->when($request->search, fn ($q, $v) => $q->where('nama_kategori', 'like', "%{$v}%")->orWhere('kode', 'like', "%{$v}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('kategori.index', compact('kategoris'));
    }

    /**
     * Tampilkan form tambah kategori (opsional).
     */
    public function create()
    {
        return view('kategori.create');
    }

    /**
     * Simpan data kategori surat baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
            'kode'          => ['required', 'string', 'max:20', 'unique:kategori_surats,kode'],
            'sifat'         => ['required', 'in:biasa,penting,rahasia'],
            'keterangan'    => ['nullable', 'string'],
        ]);

        KategoriSurat::create($data);
        ActivityLog::catat('create', 'kategori', "Menambah kategori {$data['nama_kategori']}");

        return redirect()->route('kategori.index')->with('success', 'Kategori surat berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail kategori surat (opsional).
     */
    public function show(KategoriSurat $kategori)
    {
        $kategori->loadCount(['suratMasuk', 'suratKeluar']);
        return view('kategori.show', compact('kategori'));
    }

    /**
     * Tampilkan form edit kategori surat (opsional).
     */
    public function edit(KategoriSurat $kategori)
    {
        return view('kategori.edit', compact('kategori'));
    }

    /**
     * Perbarui data kategori surat.
     */
    public function update(Request $request, KategoriSurat $kategori)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
            'kode'          => ['required', 'string', 'max:20', 'unique:kategori_surats,kode,' . $kategori->id],
            'sifat'         => ['required', 'in:biasa,penting,rahasia'],
            'keterangan'    => ['nullable', 'string'],
        ]);

        $kategori->update($data);
        ActivityLog::catat('update', 'kategori', "Mengubah kategori {$kategori->nama_kategori}");

        return redirect()->route('kategori.index')->with('success', 'Kategori surat berhasil diperbarui.');
    }

    /**
     * Hapus data kategori surat.
     */
    public function destroy(KategoriSurat $kategori)
    {
        $nama = $kategori->nama_kategori;
        $kategori->delete();
        ActivityLog::catat('delete', 'kategori', "Menghapus kategori {$nama}");

        return back()->with('success', 'Kategori surat berhasil dihapus.');
    }
}