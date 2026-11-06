<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = AuditLog::query()->with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        } elseif ($request->filled('user')) {
            $userName = $request->input('user');
            $query->whereHas('user', function ($q) use ($userName) {
                $q->where('name', $userName);
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('object_type', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->boolean('all')) {
            return AuditLogResource::collection($query->latest('created_at')->get());
        }

        $perPage = (int) $request->input('per_page', 10);
        $logs = $query->latest('created_at')->paginate($perPage);

        return AuditLogResource::collection($logs);
    }

    public function filters(): JsonResponse
    {
        $users = AuditLog::query()
            ->join('users', 'audit_logs.user_id', '=', 'users.id')
            ->distinct()
            ->pluck('users.name');

        $actions = AuditLog::query()
            ->distinct()
            ->pluck('action');

        return response()->json([
            'users'   => $users,
            'actions' => $actions,
        ]);
    }
}

