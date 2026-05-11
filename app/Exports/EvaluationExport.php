<?php

namespace App\Exports;

use App\Models\Evaluation;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class EvaluationExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(private Evaluation $evaluation) {}

    public function view(): View
    {
        return view('exports.practicum-evaluation', [
            'evaluation' => $this->evaluation,
        ]);
    }

    public function title(): string
    {
        return 'Practicum Evaluation';
    }
}
