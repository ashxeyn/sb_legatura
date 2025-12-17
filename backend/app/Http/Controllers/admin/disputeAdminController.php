<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class disputeAdminController extends Controller
{
    public function index()
    {
        $disputes = DB::table('disputes')
            ->leftJoin('projects','disputes.project_id','=','projects.project_id')
            ->select('disputes.*','projects.project_title')
            ->orderBy('disputes.created_at','desc')
            ->paginate(20);

        return view('admin.disputes.index', compact('disputes'));
    }

    public function show($id)
    {
        $dispute = DB::table('disputes')->where('id', $id)->first();
        if (!$dispute) return redirect()->route('admin.disputes.index')->with('error','Dispute not found');

        // evidence files
        $evidence = DB::table('dispute_evidences')->where('dispute_id', $id)->get();

        // related messages/comments
        $messages = DB::table('dispute_messages')->where('dispute_id', $id)->orderBy('created_at')->get();

        return view('admin.disputes.show', compact('dispute','evidence','messages'));
    }

    public function resolve($id, Request $request)
    {
        $action = $request->input('action'); // 'refund_owner', 'penalize_contractor', 'dismiss'
        $notes = $request->input('notes', null);

        DB::beginTransaction();
        try {
            DB::table('disputes')->where('id', $id)->update([
                'status' => 'resolved',
                'resolution' => $action,
                'resolution_notes' => $notes,
                'resolved_by' => session('user')->admin_user_id ?? session('user')->user_id,
                'resolved_at' => now()
            ]);
            DB::table('admin_audit_logs')->insert([
                'admin_user_id' => session('user')->admin_user_id ?? session('user')->user_id,
                'action' => "Resolved dispute {$id}",
                'meta' => json_encode(['dispute_id'=>$id,'action'=>$action]),
                'created_at'=>now()
            ]);

            // example: payment reversal or flag contractor
            if ($action === 'penalize_contractor') {
                $dispute = DB::table('disputes')->where('id',$id)->first();
                DB::table('contractors')->where('contractor_id', $dispute->contractor_id)->update(['verification_status' => 'flagged']);
            }

            DB::commit();
            return back()->with('success','Dispute resolved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error','Failed to resolve dispute: '.$e->getMessage());
        }
    }
}
