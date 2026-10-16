<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->user()->role === 'admin') {
            $totalUsers = User::count();
            $totalProjects = Project::count();
            $totalOpportunities = Project::where('status', 'planning')->count();
            $ongoingTasks = Project::where('status', 'progress')->count();
            $doneTasks = Project::where('status', 'done')->count();

            // Tambah query untuk recent projects
            $recentProjects = Project::with('endUser', 'pic')
                ->latest()
                ->limit(5)
                ->get();

            return view('dashboard.admin', compact(
                'totalUsers',
                'totalProjects',
                'totalOpportunities',
                'ongoingTasks',
                'doneTasks',
                'recentProjects'
            ));
        }

        return redirect()->route('user.dashboard');
    }
}
