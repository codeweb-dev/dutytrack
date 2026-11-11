<?php

namespace App\Support;

use App\Models\Evaluation;
use Illuminate\Http\Response;

class EvaluationDocument
{
    public static function download(Evaluation $evaluation): Response
    {
        return self::render($evaluation, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="practicum-evaluation-'.$evaluation->id.'.doc"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public static function inline(Evaluation $evaluation): Response
    {
        return self::render($evaluation, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private static function render(Evaluation $evaluation, array $headers): Response
    {
        $evaluation->loadMissing(['student.studentProfile', 'evaluator', 'assignment']);

        $logoPath = public_path('ecc_logo.jpeg');
        $logoDataUri = is_file($logoPath)
            ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        return response()
            ->view('exports.practicum-evaluation-document', [
                'evaluation' => $evaluation,
                'logoDataUri' => $logoDataUri,
            ], 200, $headers);
    }
}
