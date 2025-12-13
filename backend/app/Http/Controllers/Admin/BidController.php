<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBidRequest;
use App\Http\Requests\UpdateBidRequest;
use App\Models\Bid;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = Bid::with('contractor','project');
        if ($search = $request->input('search')) {
            $q->whereHas('project', function($qr) use ($search) {
                $qr->where('project_title', 'like', "%{$search}%");
            });
        }
        return response()->json($q->orderBy('bid_id','desc')->paginate($perPage));
    }

    public function show($id)
    {
        $bid = Bid::with('contractor','project')->find($id);
        if (!$bid) return response()->json(['error'=>'Not found'],404);
        return response()->json($bid);
    }

    public function store(StoreBidRequest $request)
    {
        $bid = Bid::create($request->validated());
        return response()->json($bid,201);
    }

    public function update(UpdateBidRequest $request, $id)
    {
        $bid = Bid::find($id);
        if (!$bid) return response()->json(['error'=>'Not found'],404);
        $bid->update($request->validated());
        return response()->json($bid);
    }

    public function destroy($id)
    {
        $bid = Bid::find($id);
        if (!$bid) return response()->json(['error'=>'Not found'],404);
        $bid->delete();
        return response()->json(['deleted'=>true]);
    }
}
