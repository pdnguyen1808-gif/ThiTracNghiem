<?php

namespace App\Http\Controllers;

use App\Http\Traits\SortableAndSearchable;
use App\Models\Nhom;
use App\Models\User;
use Illuminate\Http\Request;

class NhomController extends Controller
{
    use SortableAndSearchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $result = $this->applySortAndSearch(
            Nhom::class,
            $request,
            'tennhom',
            'Không tìm thấy nhóm'
        );

        return view('admin.nhom.index', [
            'dsNhom' => $result['data'],
            'sort' => $result['sort'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.nhom.create');
    }
    public function adduser(string $id, Request $request)
    {
        $baseQuery = User::whereNotIn('id', function ($query) use ($id) {
            $query->select('id_user')
                  ->from('chitietnhom')
                  ->where('id_nhom', $id);
        });

        $result = $this->applySortAndSearchToQuery(
            $baseQuery,
            $request,
            'name',
            'Không tìm thấy người dùng'
        );

        return view('admin.nhom.adduser', [
            'idNhom' => $id,
            'dsUser' => $result['data'],
            'sort' => $result['sort'],
            'message' => $result['message'],
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData =  $request->validate([
            'tenNhom' => 'required',
            'siSo' => 'required|integer',
            'namHoc' => 'required',
            'hocKy' => 'required',
        ]);
        $nhom = new Nhom();
        $nhom->tennhom = $request->tenNhom;
        $nhom->siso = $request->siSo;
        $nhom->namhoc = $request->namHoc;
        $nhom->hocky = $request->hocKy;
        $nhom->save();
        return redirect()->route('nhom.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $nhom = Nhom::find($id);
        $result = $this->applySortAndSearchToRelation(
            $nhom->users(),
            $request,
            'name',
            'Không tìm thấy người dùng'
        );

        return view('admin.nhom.detail', [
            'dsUser' => $result['data'],
            'sort' => $result['sort'],
            'nhom' => $nhom,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $nhom = Nhom::find($id);
        return view('admin.nhom.edit', ['nhom' => $nhom]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData =  $request->validate([
            'tenNhom' => 'required',
            'siSo' => 'required|integer',
            'namHoc' => 'required',
            'hocKy' => 'required',
        ]);
        $nhom = Nhom::find($id);
        $nhom->tennhom = $request->tenNhom;
        $nhom->siso = $request->siSo;
        $nhom->namhoc = $request->namHoc;
        $nhom->hocky = $request->hocKy;
        $nhom->save();
        return redirect()->route('nhom.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $nhom = Nhom::find($id);
        $nhom->delete();
        return redirect()->route('nhom.index');
    }
}
