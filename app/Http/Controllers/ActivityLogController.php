<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:activity_log.view')->only(['index']);
    }

    public function index(Request $request): Response
    {
        $query = Activity::with(['causer', 'subject'])->orderBy('created_at', 'desc');

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->integer('causer_id'));
        }

        if ($request->filled('log_name')) {
            $query->inLog($request->string('log_name')->toString());
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from')->toString());
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to')->toString());
        }

        $activities = $query->paginate(20)->appends($request->query());

        return Inertia::render('ActivityLog/Index', [
            'activities' => $activities,
            'filters' => [
                'causer_id' => $request->integer('causer_id') ?: null,
                'log_name' => $request->string('log_name')->toString(),
                'from' => $request->string('from')->toString(),
                'to' => $request->string('to')->toString(),
            ],
            'users' => User::orderBy('name')->get(['id', 'name']),
            'logNames' => Activity::query()->distinct()->orderBy('log_name')->pluck('log_name'),
        ]);
    }
}
