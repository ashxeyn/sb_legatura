<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\storeMilestoneRequest;
use App\Http\Requests\admin\updateMilestoneRequest;
use App\Models\admin\milestoneClass;
use Illuminate\Http\Request;

class milestoneController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)$request->input('per_page',15);
        $q = milestoneClass::query();
        if ($project = $request->input('project_id')) {
            $q->where('project_id',$project);
        }
        return response()->json($q->orderBy('milestone_id','desc')->paginate($perPage));
    }

    public function show($id)
    {
        $m = milestoneClass::find($id);
        if (!$m) return response()->json(['error'=>'Not found'],404);
        return response()->json($m);
    }

    public function store(storeMilestoneRequest $request)
    {
        $m = milestoneClass::create($request->validated());
        return response()->json($m,201);
    }

    public function update(updateMilestoneRequest $request, $id)
    {
        $m = milestoneClass::find($id);
        if (!$m) return response()->json(['error'=>'Not found'],404);
        $m->update($request->validated());
        return response()->json($m);
    }

    public function destroy($id)
    {
        $m = milestoneClass::find($id);
        if (!$m) return response()->json(['error'=>'Not found'],404);
        $m->delete();
        return response()->json(['deleted'=>true]);
    }
}
