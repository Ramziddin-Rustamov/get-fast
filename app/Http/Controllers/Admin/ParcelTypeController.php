<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\V1\ParcelType;
use Illuminate\Http\Request;

class ParcelTypeController extends Controller
{
    /**
     * Pochta turlari ro'yxati.
     */
    public function index()
    {
        $parcelTypes = ParcelType::latest()->paginate(15);

        return view('admin-views.parcel-types.index', compact('parcelTypes'));
    }

    /**
     * Yangi tur qo'shish formasi.
     */
    public function create()
    {
        return view('admin-views.parcel-types.create');
    }

    /**
     * Yangi turni saqlash.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        ParcelType::create($data);

        return redirect()
            ->route('parcel-types.index')
            ->with('success', 'Pochta turi qo\'shildi.');
    }

    /**
     * Turni tahrirlash formasi.
     */
    public function edit(ParcelType $parcelType)
    {
        return view('admin-views.parcel-types.edit', compact('parcelType'));
    }

    /**
     * Turni yangilash.
     */
    public function update(Request $request, ParcelType $parcelType)
    {
        $data = $this->validateData($request);

        $parcelType->update($data);

        return redirect()
            ->route('parcel-types.index')
            ->with('success', 'Pochta turi yangilandi.');
    }

    /**
     * Turni o'chirish.
     */
    public function destroy(ParcelType $parcelType)
    {
        $parcelType->delete();

        return redirect()
            ->route('parcel-types.index')
            ->with('success', 'Pochta turi o\'chirildi.');
    }

    /**
     * Umumiy validatsiya (store + update).
     */
    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'name_uz' => ['required', 'string', 'max:255'],
            'name_ru' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'icon'    => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
