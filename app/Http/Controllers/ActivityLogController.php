<?php
namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('permission:ver logs')->only(['index', 'data']);
    }

    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(15)->withQueryString();

        $logNames = Activity::query()
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');

        $events = Activity::query()
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        $subjectTypes = Activity::query()
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type');

        return view('activity_logs.index', [
            'logs' => $logs,
            'logNames' => $logNames,
            'events' => $events,
            'subjectTypes' => $subjectTypes,
        ]);
    }

    public function data(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])->select('activity_log.*');

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->addColumn('usuario', function ($log) {
                return $log->causer
                    ? ($log->causer->name ?? 'Sin nombre') . ' (#' . $log->causer_id . ')'
                    : 'Sistema / Nulo';
            })
            ->addColumn('subject', function ($log) {
                return $log->subject_type
                    ? class_basename($log->subject_type) . ' #' . $log->subject_id
                    : 'Sin subject';
            })
            ->addColumn('fecha', function ($log) {
                return optional($log->created_at)->format('d/m/Y H:i:s');
            })
            ->addColumn('propiedades', function ($log) {
                return '<button 
                            type="button" 
                            class="btn btn-sm btn-info ver-propiedades"
                            data-propiedades=\'' . e(json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '\'>
                            Ver
                        </button>';
            })
            ->rawColumns(['propiedades'])
            ->make(true);
    }
}
