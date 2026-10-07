<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Project yang user pegang (sebagai PIC)
        $myProjects = Project::where('pic_id', $userId)
            ->count();

        // Task dengan status progress (ongoing)
        $myOngoing = Project::where('pic_id', $userId)
            ->where('status', 'progress')
            ->count();

        // Task dengan status done
        $myDone = Project::where('pic_id', $userId)
            ->where('status', 'done')
            ->count();

        // Project dengan deadline dekat (kurang dari 7 hari ke depan)
        $nearDeadline = Project::where('pic_id', $userId)
            ->where('status', '!=', 'done')
            ->where('status', '!=', 'cancel')
            ->whereBetween('deadline_date', [
                Carbon::now(),
                Carbon::now()->addDays(7)
            ])
            ->count();

        // Task terbaru (ambil 5 terbaru)
        $tasks = Project::where('pic_id', $userId)
            ->with('endUser')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.user', compact(
            'myProjects',
            'myOngoing',
            'myDone',
            'nearDeadline',
            'tasks'
        ));
    }
}
