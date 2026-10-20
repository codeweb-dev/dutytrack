@php
    use App\Support\PracticumEvaluationForm;

    $student = $evaluation->student;
    $profile = $student?->studentProfile;
    $evaluator = $evaluation->evaluator;
    $ratings = $evaluation->ratings ?? [];
    $evaluatorType = 'Supervisor';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Practicum Student Evaluation Form</title>
    <style>
        @page WordSection1 {
            size: 8.5in 11in;
            margin: 0.25in;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
        }

        div.WordSection1 {
            page: WordSection1;
            padding: 22pt 34pt 18pt;
        }

        p {
            margin: 0;
            line-height: 1.12;
        }

        .title {
            text-align: center;
            margin: 0 0 12pt;
            font-weight: bold;
        }

        .field-table,
        .rating-table,
        .grade-table,
        .signature-table {
            border-collapse: collapse;
            width: 100%;
        }

        .field-table td {
            padding: 0 4pt 1pt 0;
            vertical-align: bottom;
        }

        .label {
            white-space: nowrap;
        }

        .line {
            border-bottom: 1px solid #000;
            height: 14pt;
        }

        .hint {
            font-size: 8pt;
            font-style: italic;
            text-align: center;
        }

        .directions {
            margin-top: 9pt;
            margin-bottom: 7pt;
        }

        .directions em {
            font-size: 10pt;
        }

        .rating-table td {
            padding: 1.5pt 4pt;
            vertical-align: top;
        }

        .area-title {
            width: 68%;
        }

        .weight {
            width: 12%;
            text-align: center;
        }

        .gap {
            width: 4%;
        }

        .rating {
            width: 16%;
            text-align: center;
        }

        .rating-line {
            border-bottom: 1px solid #bfbfbf;
            text-align: center;
        }

        .subtotal-label,
        .total-label {
            text-align: right;
        }

        .spacer td {
            height: 5pt;
            padding: 0;
        }

        .grade-title {
            margin-top: 8pt;
            margin-bottom: 1pt;
        }

        .grade-table td {
            width: 16.66%;
            padding: 1pt 4pt 1pt 0;
            white-space: nowrap;
        }

        .comments {
            margin-top: 8pt;
        }

        .comment-lines {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2pt;
        }

        .comment-lines td {
            border-bottom: 1px solid #000;
            height: 16pt;
            vertical-align: bottom;
        }

        .signature-table {
            margin-top: 8pt;
        }

        .signature-table td {
            padding: 1pt 4pt;
            vertical-align: bottom;
        }

        .signature-note {
            font-size: 8pt;
            text-align: center;
        }

        .school-header {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
            color: #000;
            margin-bottom: 0;
        }

        .school-header td {
            padding: 8pt 10pt 7pt;
            vertical-align: middle;
        }

        .school-logo-cell {
            width: 24%;
            text-align: right;
        }

        .school-text-cell {
            width: 76%;
            text-align: center;
        }

        .school-logo {
            width: 90px;
            height: 88px;
        }

        .school-name {
            font-family: Arial, sans-serif;
            font-size: 16pt;
            font-weight: bold;
            line-height: 1.08;
        }

        .campus {
            font-family: Arial, sans-serif;
            font-size: 20pt;
            font-weight: bold;
            letter-spacing: 6pt;
            line-height: 1.05;
        }

        .school-republic,
        .school-address {
            font-size: 14pt;
            line-height: 1.08;
        }
    </style>
</head>
<body>
    <div class="WordSection1">
        <table class="school-header">
            <tr>
                <td class="school-logo-cell">
                    @if ($logoDataUri)
                        <img class="school-logo" src="{{ $logoDataUri }}" alt="EARIST Cavite Campus logo">
                    @endif
                </td>
                <td class="school-text-cell">
                    <p class="school-republic">Republic of the Philippines</p>
                    <p class="school-name">EULOGIO &ldquo;AMANG&rdquo; RODRIGUEZ</p>
                    <p class="school-name">INSTITUTE OF SCIENCE AND TECHNOLOGY</p>
                    <p class="campus">Cavite Campus</p>
                    <p class="school-address">General Mariano Alvarez, Cavite</p>
                </td>
            </tr>
        </table>

        <p class="title">(Practicum-Student Evaluation Form)</p>

        <table class="field-table">
            <tr>
                <td class="label" style="width: 8%;">Name:</td>
                <td class="line" style="width: 32%;">{{ $student?->name }}</td>
                <td class="label" style="width: 8%;">Course:</td>
                <td class="line" style="width: 20%;">{{ $profile?->course }}</td>
                <td class="label" style="width: 14%;">Inclusive Date:</td>
                <td class="line" style="width: 9%;">{{ $evaluation->inclusive_from?->format('m/d/Y') }}</td>
                <td class="line" style="width: 9%;">{{ $evaluation->inclusive_to?->format('m/d/Y') }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="hint">Intern</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="hint">From</td>
                <td class="hint">To</td>
            </tr>
            <tr>
                <td class="label" colspan="2">Cooperating Program:</td>
                <td class="line" colspan="5">{{ $evaluation->cooperating_program }}</td>
            </tr>
        </table>

        <div class="directions">
            <p>Directions for Evaluation:</p>
            <p><em>Please rate the trainee according to the different areas / job factors below by indicating the corresponding points.</em></p>
        </div>

        <table class="rating-table">
            @foreach (PracticumEvaluationForm::areas() as $areaKey => $area)
                <tr>
                    <td class="area-title" colspan="2">{{ $area['title'] }}</td>
                    <td class="weight">Weight</td>
                    <td class="gap"></td>
                    <td class="rating">Rating</td>
                </tr>
                <tr class="spacer"><td colspan="5"></td></tr>

                @foreach ($area['items'] as $itemKey => $label)
                    <tr>
                        <td style="width: 4%;"></td>
                        <td>{{ $label }}</td>
                        <td class="weight">5.0</td>
                        <td class="gap"></td>
                        <td class="rating-line">{{ number_format((float) ($ratings[$areaKey][$itemKey] ?? 0), 1) }}</td>
                    </tr>
                @endforeach

                <tr class="spacer"><td colspan="5"></td></tr>
                <tr>
                    <td></td>
                    <td class="subtotal-label">Subtotal</td>
                    <td class="weight">{{ number_format($area['subtotal'], 1) }}</td>
                    <td class="gap"></td>
                    <td class="rating-line">{{ number_format(PracticumEvaluationForm::areaTotal($ratings, $areaKey), 1) }}</td>
                </tr>
                <tr class="spacer"><td colspan="5"></td></tr>
            @endforeach

            <tr>
                <td></td>
                <td class="total-label">TOTAL RATING</td>
                <td class="weight">100.0</td>
                <td class="gap"></td>
                <td class="rating-line">{{ number_format((float) $evaluation->rating, 1) }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="total-label">EQUIVALENT GRADE</td>
                <td></td>
                <td class="gap"></td>
                <td class="rating-line">{{ PracticumEvaluationForm::gradeEquivalent((float) $evaluation->rating) }}</td>
            </tr>
        </table>

        <p class="grade-title">Grade Equivalent</p>
        <table class="grade-table">
            <tr>
                <td>97 - 100</td><td>- 1.0</td>
                <td>88 - 90</td><td>- 1.75</td>
                <td>79 - 81</td><td>- 2.5</td>
            </tr>
            <tr>
                <td>94 - 96</td><td>- 1.25</td>
                <td>85 - 87</td><td>- 2.0</td>
                <td>76 - 78</td><td>- 2.75</td>
            </tr>
            <tr>
                <td>91 - 93</td><td>- 1.5</td>
                <td>82 - 84</td><td>- 2.25</td>
                <td>73 - 75</td><td>- 3.0</td>
            </tr>
            <tr>
                <td>72 - below</td><td>- 5.0</td>
                <td></td><td></td>
                <td></td><td></td>
            </tr>
        </table>

        <div class="comments">
            <p>Comments / Suggestions</p>
            <table class="comment-lines">
                <tr><td>{{ $evaluation->remarks }}</td></tr>
                <tr><td></td></tr>
                <tr><td></td></tr>
            </table>
        </div>

        <table class="signature-table">
            <tr>
                <td style="width: 13%;">Rated by:</td>
                <td class="line" style="width: 45%; text-align: center;">{{ $evaluator?->name }}</td>
                <td style="width: 4%;"></td>
                <td class="line" style="width: 38%; text-align: center;">{{ $evaluatorType }} / {{ $evaluation->evaluation_date?->format('m/d/Y') }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="signature-note">Signature over printed name</td>
                <td></td>
                <td class="signature-note">Position / Date</td>
            </tr>
        </table>

    </div>
</body>
</html>
