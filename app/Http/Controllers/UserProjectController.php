<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class UserProjectController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $projects = Project::with('endUser')
            ->where('pic_id', $userId)
            ->latest()
            ->paginate(10);

        $stats = Project::where('pic_id', $userId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'progress') as progress,
                SUM(status = 'done') as done
            ")
            ->first();

        $totalProject = (int) $stats->total;
        $progress     = (int) $stats->progress;
        $done         = (int) $stats->done;

        return view('user.projects.index', compact(
            'projects',
            'totalProject',
            'progress',
            'done'
        ));
    }

    public function update(Request $request, Project $project)
    {
        abort_if((int) $project->pic_id !== (int) auth()->id(), 403);

        abort_if(
            $project->status === 'cancel',
            403,
            'Project yang sudah dibatalkan tidak bisa diubah.'
        );

        $request->validate([
            'status' => 'required|in:planning,progress,on_hold,done',
        ]);

        $project->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Progress project berhasil diperbarui');
    }
}
