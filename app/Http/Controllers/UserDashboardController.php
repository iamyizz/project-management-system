<?php

namespace App\Http\Controllers;

use App\Models\Project;

class UserDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $assignedProjects = Project::query()->where('pic_id', $userId);
        $myProjects = (clone $assignedProjects)->count();
        $myOngoing = (clone $assignedProjects)->whereIn('status', ['planning', 'progress'])->count();
        $myDone = (clone $assignedProjects)->where('status', 'done')->count();

        $priorityQuery = Project::query()
            ->where('pic_id', $userId)
            ->whereIn('status', ['planning', 'progress'])
            ->whereNotNull('deadline_date')
            ->whereDate('deadline_date', '<=', today()->addDays(7));

        $needsAttention = (clone $priorityQuery)->count();
        $priorityProjects = (clone $priorityQuery)
            ->with('endUser')
            ->orderBy('deadline_date')
            ->limit(5)
            ->get();

        $recentProjects = (clone $assignedProjects)
            ->with('endUser')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('dashboard.user', compact(
            'myProjects',
            'myOngoing',
            'myDone',
            'needsAttention',
            'priorityProjects',
            'recentProjects',
        ));
    }
}
