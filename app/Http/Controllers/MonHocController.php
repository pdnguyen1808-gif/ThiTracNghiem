<?php

namespace App\Http\Controllers;

use App\Http\Traits\SortableAndSearchable;
use App\Models\MonHoc;
use Illuminate\Http\Request;

class MonHocController extends Controller
{
    use SortableAndSearchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $result = $this->applySortAndSearch(
            MonHoc::class,
            $request,
            'tenmon',
            'Không tìm thấy môn học'
        );

        return view('admin.monhoc.index', [
            'dsMonHoc' => $result['data'],
            'sort' => $result['sort'],
            'message' => $result['message'],
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.monhoc.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData =  $request->validate([
            'tenMon' => 'required',
        ]);
        $monHoc = new MonHoc();
        $monHoc->tenmon = $request->tenMon;
        $monHoc->save();
        return redirect()->route('monhoc.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $monHoc = MonHoc::find($id);
        return view('admin.monhoc.edit', ['monHoc' => $monHoc]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData =  $request->validate([
            'tenMon' => 'required',
        ]);
        $monHoc = MonHoc::find($id);
        $monHoc->tenmon = $request->tenMon;
        $monHoc->save();
        return redirect()->route('monhoc.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $monHoc = MonHoc::find($id);
        $monHoc->delete();
        return redirect()->route('monhoc.index');
    }
}
