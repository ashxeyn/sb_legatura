<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\storeBidRequest;
use App\Http\Requests\admin\updateBidRequest;
use App\Models\admin\bidClass;
use Illuminate\Http\Request;

class bidController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = bidClass::with('contractor','project');
        if ($search = $request->input('search')) {
            $q->whereHas('project', function($qr) use ($search) {
                $qr->where('project_title', 'like', "%{$search}%");
            });
        }
        return response()->json($q->orderBy('bid_id','desc')->paginate($perPage));
    }

    public function show($id)
    {
        $bid = bidClass::with('contractor','project')->find($id);
        if (!$bid) return response()->json(['error'=>'Not found'],404);
        return response()->json($bid);
    }

    public function store(storeBidRequest $request)
    {
        $bid = bidClass::create($request->validated());
        return response()->json($bid,201);
    }

    public function update(updateBidRequest $request, $id)
    {
        $bid = bidClass::find($id);
        if (!$bid) return response()->json(['error'=>'Not found'],404);
        $bid->update($request->validated());
        return response()->json($bid);
    }

    public function destroy($id)
    {
        $bid = bidClass::find($id);
        if (!$bid) return response()->json(['error'=>'Not found'],404);
        $bid->delete();
        return response()->json(['deleted'=>true]);
    }
}
