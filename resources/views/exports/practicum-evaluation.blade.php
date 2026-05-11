@php
    use App\Support\PracticumEvaluationForm;

    $student = $evaluation->student;
    $profile = $student?->studentProfile;
    $evaluator = $evaluation->evaluator;
    $ratings = $evaluation->ratings ?? [];
@endphp

<table>
    <tr>
        <td colspan="4" style="text-align: center; font-weight: bold;">(Practicum-Student Evaluation Form)</td>
    </tr>
    <tr><td colspan="4"></td></tr>
    <tr>
        <td style="font-weight: bold;">Name:</td>
        <td>{{ $student?->name }}</td>
        <td style="font-weight: bold;">Course:</td>
        <td>{{ $profile?->course }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Inclusive Date:</td>
        <td>{{ $evaluation->inclusive_from?->format('M d, Y') }}</td>
        <td style="font-weight: bold;">To:</td>
        <td>{{ $evaluation->inclusive_to?->format('M d, Y') }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Cooperating Program:</td>
        <td colspan="3">{{ $evaluation->cooperating_program }}</td>
    </tr>
    <tr><td colspan="4"></td></tr>
    <tr>
        <td colspan="4" style="font-weight: bold;">Directions for Evaluation:</td>
    </tr>
    <tr>
        <td colspan="4">Please rate the trainee according to the different areas / job factors below by indicating the corresponding points.</td>
    </tr>
    <tr><td colspan="4"></td></tr>

    @foreach (PracticumEvaluationForm::areas() as $areaKey => $area)
        <tr>
            <td colspan="2" style="font-weight: bold;">{{ $area['title'] }}</td>
            <td style="text-align: center; font-weight: bold;">Weight</td>
            <td style="text-align: center; font-weight: bold;">Rating</td>
        </tr>

        @foreach ($area['items'] as $itemKey => $label)
            <tr>
                <td colspan="2">{{ $label }}</td>
                <td style="text-align: center;">5.0</td>
                <td style="text-align: center;">{{ number_format((float) ($ratings[$areaKey][$itemKey] ?? 0), 1) }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="2" style="text-align: right; font-weight: bold;">Subtotal</td>
            <td style="text-align: center; font-weight: bold;">{{ number_format($area['subtotal'], 1) }}</td>
            <td style="text-align: center; font-weight: bold;">{{ number_format(PracticumEvaluationForm::areaTotal($ratings, $areaKey), 1) }}</td>
        </tr>
        <tr><td colspan="4"></td></tr>
    @endforeach

    <tr>
        <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL RATING</td>
        <td style="text-align: center; font-weight: bold;">100.0</td>
        <td style="text-align: center; font-weight: bold;">{{ number_format((float) $evaluation->rating, 1) }}</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: right; font-weight: bold;">EQUIVALENT GRADE</td>
        <td colspan="2" style="text-align: center; font-weight: bold;">{{ PracticumEvaluationForm::gradeEquivalent((float) $evaluation->rating) }}</td>
    </tr>
    <tr><td colspan="4"></td></tr>
    <tr>
        <td colspan="4" style="font-weight: bold;">Grade Equivalent</td>
    </tr>
    <tr>
        <td>97 - 100 = 1.0</td>
        <td>94 - 96 = 1.25</td>
        <td>91 - 93 = 1.5</td>
        <td>88 - 90 = 1.75</td>
    </tr>
    <tr>
        <td>85 - 87 = 2.0</td>
        <td>82 - 84 = 2.25</td>
        <td>79 - 81 = 2.5</td>
        <td>76 - 78 = 2.75</td>
    </tr>
    <tr>
        <td>73 - 75 = 3.0</td>
        <td>72 - below = 5.0</td>
        <td></td>
        <td></td>
    </tr>
    <tr><td colspan="4"></td></tr>
    <tr>
        <td colspan="4" style="font-weight: bold;">Comments / Suggestions</td>
    </tr>
    <tr>
        <td colspan="4">{{ $evaluation->remarks }}</td>
    </tr>
    <tr><td colspan="4"></td></tr>
    <tr>
        <td style="font-weight: bold;">Rated by:</td>
        <td>{{ $evaluator?->name }}</td>
        <td style="font-weight: bold;">Position / Date:</td>
        <td>{{ str_replace('_', ' ', ucfirst($evaluation->evaluator_type)) }} / {{ $evaluation->evaluation_date?->format('M d, Y') }}</td>
    </tr>
    <tr><td colspan="4"></td></tr>
    <tr>
        <td colspan="4" style="text-align: center; font-weight: bold;">Republic of the Philippines</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center; font-weight: bold;">EULOGIO "AMANG" RODRIGUEZ INSTITUTE OF SCIENCE AND TECHNOLOGY</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center;">Cavite Campus</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center;">General Mariano Alvarez, Cavite</td>
    </tr>
</table>
