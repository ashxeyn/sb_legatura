<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\storeBidRequest;
use App\Http\Requests\admin\updateBidRequest;
use App\Models\admin\bid;
use Illuminate\Http\Request;
use App\Services\AdminActivityLog;

class bidController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = bid::with('contractor','project');
        if ($search = $request->input('search')) {
            $q->whereHas('project', function($qr) use ($search) {
                $qr->where('project_title', 'like', "%{$search}%");
            });
        }
        return response()->json($q->orderBy('bid_id','desc')->paginate($perPage));
    }

    public function show($id)
    {
        $bid = bid::with('contractor','project')->find($id);
        if (!$bid) return response()->json(['error'=>'Not found'],404);
        return response()->json($bid);
    }

    public function store(storeBidRequest $request)
    {
        $bid = bid::create($request->validated());
        AdminActivityLog::log('bid_created', ['bid_id' => $bid->bid_id ?? $bid->id]);
        return response()->json($bid,201);
    }

    public function update(updateBidRequest $request, $id)
    {
        $bid = bid::find($id);
        if (!$bid) return response()->json(['error'=>'Not found'],404);
        $bid->update($request->validated());
        AdminActivityLog::log('bid_updated_direct', ['bid_id' => $id]);
        return response()->json($bid);
    }

    public function destroy($id)
    {
        $bid = bid::find($id);
        if (!$bid) return response()->json(['error'=>'Not found'],404);
        $bid->delete();
        AdminActivityLog::log('bid_deleted_direct', ['bid_id' => $id]);
        return response()->json(['deleted'=>true]);
    }
}
