<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            $totalUsers = User::count();
            $totalProjects = Project::count();
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
                'ongoingTasks',
                'doneTasks',
                'recentProjects'
            ));
        }

        return view('dashboard.user');
    }
}
