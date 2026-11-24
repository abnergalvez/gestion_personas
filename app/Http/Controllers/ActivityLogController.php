<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        $logs = ActivityLog::query()
            ->with('user')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', '%'.$search.'%')
                        ->orWhere('user_name', 'like', '%'.$search.'%')
                        ->orWhere('user_email', 'like', '%'.$search.'%')
                        ->orWhere('ip_address', 'like', '%'.$search.'%');
                });
            })
            ->when($request->string('action')->value(), fn ($query, $action) => $query->where('action', $action))
            ->when($request->integer('user_id') ?: null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($this->day($request, 'from'), fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($this->day($request, 'to'), fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('activity-logs.index', [
            'logs' => $logs,
            'actions' => ActivityAction::options(),
            'users' => User::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function show(ActivityLog $activityLog): View
    {
        $activityLog->load('user');

        return view('activity-logs.show', ['log' => $activityLog]);
    }

    private function day(Request $request, string $key): ?string
    {
        $value = $request->string($key)->toString();

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }
}
