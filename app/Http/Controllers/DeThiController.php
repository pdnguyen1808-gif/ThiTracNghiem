<?php

namespace App\Http\Controllers;

use App\Http\Traits\SortableAndSearchable;
use App\Models\ChiTietNhom;
use App\Models\DeThi;
use App\Models\GiaoDeThi;
use App\Models\KetQua;
use App\Models\MonHoc;
use App\Models\Nhom;
use Illuminate\Http\Request;

class DeThiController extends Controller
{
    use SortableAndSearchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $result = $this->applySortAndSearch(
            DeThi::class,
            $request,
            'tende',
            'Không tìm thấy nhóm'
        );

        return view('admin.dethi.index', [
            'dsDeThi' => $result['data'],
            'sort' => $result['sort'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dsMonHoc = MonHoc::get();
        $dsNhom = Nhom::get();
        return view('admin.dethi.create', ['dsMonHoc' => $dsMonHoc, 'dsNhom' => $dsNhom]);
    }
    public function xemdiem(Request $request,string $id)
    {
        $deThi = DeThi::find($id);
        $dsUser = $deThi->users;


        if ($request->get('searchKey')) {
            $searchKey = strtolower($request->get('searchKey'));
            $dsUser = DeThi::find($id)->users()->whereRaw('LOWER(name) LIKE ?', ['%' . $searchKey . '%'])->get();
            $message = count($dsUser) == 0 ? 'Không tìm thấy người dùng' : null;
        }
        $sort = $request->get('sort');

       $dsUser->map(function ($user) use ($deThi) {
            $ketqua = KetQua::where('id_user',$user->id)->where('id_dethi',$deThi->id)->first();
            $user->diem = $ketqua->diem;
            return $user;
        });
        if ($request->get('sort')['enabel']) {
            $column = $request->get('sort')['column'];
            $type = $request->get('sort')['type'];
            if($type=='desc'){
                $dsUser =  $dsUser->sortByDesc($column)->all();
            }else{
                $dsUser =  $dsUser->sortBy($column)->all();
            }
        }


        $dsNhom = Nhom::get();
        return view('admin.dethi.xemdiem', ['deThi'=>$deThi,'dsUser' =>  $dsUser,'sort'=>$sort]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tende' => 'required|string|max:255',
            'tglambai' => 'required|integer|min:1',
            'tgbatdau' => 'required|date|before:tgketthuc',
            'tgketthuc' => 'required|date',
            'idMonHoc' => 'required|integer|exists:monhoc,id',
            'socaude' => 'required|integer|min:1',
            'socautrungbinh' => 'required|integer|min:0',
            'socaukho' => 'required|integer|min:0',
        ]);

        $deThi = new DeThi();
        $deThi->tende = $request->tende;
        $deThi->tgthi = $request->tglambai;
        $deThi->tgmode = $request->tgbatdau;
        $deThi->tgketthuc = $request->tgketthuc;
        $deThi->troncauhoi = $request->troncauhoi === 'on' ? true : false;
        $deThi->trondapan = $request->trondapan === 'on' ? true : false;
        $deThi->xemdiemthi = $request->xemdiemthi === 'on' ? true : false;
        $deThi->id_monhoc = $request->idMonHoc;
        $deThi->socaude = $request->socaude;
        $deThi->socautrungbinh = $request->socautrungbinh;
        $deThi->socaukho = $request->socaukho;
        $deThi->save();

        if (isset($request->dsNhom)) {
            foreach ($request->dsNhom as $nhom) {
                GiaoDeThi::create([
                    'id_nhom' => $nhom,
                    'id_dethi' => $deThi->id
                ]);
            }
        }
        return redirect()->route('dethi.addcauhoi', ['id' => $deThi->id]);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $deThi = DeThi::find($id);
        $result = $this->applySortAndSearchToRelation(
            $deThi->nhoms(),
            $request,
            'name',
            'Không tìm thấy người dùng'
        );

        $excludedNhomIds = $deThi->nhoms->pluck('id')->toArray();
        $dsNhomNotInDeThi = Nhom::whereNotIn('id', $excludedNhomIds)->get();

        return view('admin.dethi.detail', [
            'dsNhom' => $result['data'],
            'sort' => $result['sort'],
            'deThi' => $deThi,
            'dsNhomNotInDeThi' => $dsNhomNotInDeThi,
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $deThi = DeThi::find($id);
        $dsMonHoc = MonHoc::get();
        $dsNhom = Nhom::get();
        return view('admin.dethi.edit', ['dsMonHoc' => $dsMonHoc, 'dsNhom' => $dsNhom, 'deThi' => $deThi]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tende' => 'required|string|max:255',
            'tglambai' => 'required|integer|min:1',
            'tgbatdau' => 'required|date|before:tgketthuc',
            'tgketthuc' => 'required|date',
            'idMonHoc' => 'required|integer|exists:monhoc,id',
            'socaude' => 'required|integer|min:1',
            'socautrungbinh' => 'required|integer|min:0',
            'socaukho' => 'required|integer|min:0',
        ]);

        $deThi = DeThi::find($id);
        $deThi->tende = $request->tende;
        $deThi->tgthi = $request->tglambai;
        $deThi->tgmode = $request->tgbatdau;
        $deThi->tgketthuc = $request->tgketthuc;
        $deThi->troncauhoi = $request->troncauhoi === 'on' ? true : false;
        $deThi->trondapan = $request->trondapan === 'on' ? true : false;
        $deThi->xemdiemthi = $request->xemdiemthi === 'on' ? true : false;
        $deThi->id_monhoc = $request->idMonHoc;
        $deThi->socaude = $request->socaude;
        $deThi->socautrungbinh = $request->socautrungbinh;
        $deThi->socaukho = $request->socaukho;
        $deThi->save();
        return redirect()->route('dethi.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deThi = DeThi::find($id);
        $deThi->delete();
        return redirect()->route('dethi.index');
    }
}
