<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reports;

    public function __construct(ReportService $reports)
    {
        $this->reports = $reports;   // ✅ Dependency Injection
    }

    public function index(Request $request)
    {
        $type = $request->query('type');  // top5, occupancy, power-users

        return match ($type) {
            'top5' => response()->json(['data' => $this->reports->top5()]),
            'power-users' => response()->json(['data' => $this->reports->powerUsers()]),
            'occupancy' => response()->json(['data' => $this->reports->occupancy()]),
            default => response()->json(['error' => 'Invalid report type'], 400)
        };
    }
}
