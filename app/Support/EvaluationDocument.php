<?php

namespace App\Support;

use App\Models\Evaluation;
use Illuminate\Http\Response;

class EvaluationDocument
{
    public static function download(Evaluation $evaluation): Response
    {
        $evaluation->loadMissing(['student.studentProfile', 'evaluator', 'assignment']);

        $filename = 'practicum-evaluation-'.$evaluation->id.'.doc';
        $logoPath = public_path('ecc_logo.jpeg');
        $logoDataUri = is_file($logoPath)
            ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        return response()
            ->view('exports.practicum-evaluation-document', [
                'evaluation' => $evaluation,
                'logoDataUri' => $logoDataUri,
            ], 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'max-age=0',
            ]);
    }
}
