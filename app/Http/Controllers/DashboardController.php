<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\ControlHorasService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ControlHorasService $controlHorasService
    ) {
    }

    public function index(): View
    {
        return view('dashboard', [
            'controlHorasSummary' => $this->controlHorasService->getSummaryForUser((int) auth()->id()),
            'controlHorasRows' => $this->controlHorasService->getRowsForUser((int) auth()->id()),
        ]);
    }

    public function controlHorasPreview()
    {
        $previewData = $this->controlHorasService->getPreviewDataForUser((int) auth()->id());

        return Pdf::loadView('dashboard.partials.control-horas-preview-document', $previewData)
            ->setPaper('letter', 'landscape')
            ->stream('control-horas.pdf');
    }
}
