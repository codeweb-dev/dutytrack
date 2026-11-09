{{-- Recreates EARIST_OJT_Certificate_Template.docx (A4 landscape) with the student's details filled in. --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Certificate of Completion — {{ $name }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: Times, serif; color: #3c3c3c; font-size: 12pt; }
        p { margin: 0; text-align: center; }
        table { border-collapse: collapse; }
        td { padding: 0; }
        .border { position: fixed; top: 0; left: 0; width: 297mm; height: 210mm; }
        .page { position: relative; padding: 22mm 24mm 0; }
        .header td { vertical-align: middle; }
        .school { text-align: center; color: #111; line-height: 1.25; }
        .school .small { font-size: 10pt; }
        .school .big { font-size: 11pt; font-weight: bold; }
        .title { margin-top: 12mm; font-size: 25pt; font-weight: bold; color: #303030; letter-spacing: 0.5pt; }
        .subtitle { margin-top: 2mm; font-size: 18pt; color: #a97839; }
        .lead { margin-top: 7mm; }
        .name { margin: 6mm auto 0; width: 165mm; border-bottom: 1pt solid #9d6f3c; padding-bottom: 1mm; font-size: 24pt; font-weight: bold; color: #9d6f3c; }
        .body { margin-top: 8mm; line-height: 1.6; }
        .fill { font-weight: bold; color: #111; border-bottom: 0.75pt solid #3c3c3c; padding: 0 4pt; }
        .signatures { margin: 22mm auto 0; width: 220mm; }
        .signatures td { width: 50%; text-align: center; vertical-align: bottom; }
        .signer { height: 6mm; font-weight: bold; text-transform: uppercase; color: #111; }
        .line { margin: 0 auto; width: 75mm; border-top: 1pt solid #111; padding-top: 2pt; font-size: 10pt; }
    </style>
</head>

@php($blank = fn ($value, $width = 40) => $value !== null && $value !== '' ? e($value) : str_repeat('&nbsp;', $width))

<body>
    <img class="border" src="{{ $border }}" alt="">

    <div class="page">
        <table class="header" align="center">
            <tr>
                <td style="padding-right: 6mm;"><img src="{{ $logo }}" alt="EARIST" style="width: 21.6mm; height: 21.1mm;"></td>
                <td class="school">
                    <div class="small">Republic of the Philippines</div>
                    <div class="big">EULOGIO “AMANG” RODRIGUEZ INSTITUTE OF SCIENCE AND TECHNOLOGY</div>
                    <div class="big">CAVITE BRANCH</div>
                    <div class="small">General Mariano Alvarez, Cavite</div>
                </td>
                <td style="width: 27.6mm;"></td>
            </tr>
        </table>

        <p class="title">CERTIFICATE OF COMPLETION</p>
        <p class="subtitle">ON-THE-JOB TRAINING (OJT)</p>
        <p class="lead">This is to formally certify that</p>
        <p class="name">{{ $name }}</p>

        <div class="body">
            <p>
                has successfully completed <span class="fill">{{ $hours }} hours</span> of On-the-Job Training (OJT) at
                <span class="fill">{!! $blank($company) !!}</span>,
            </p>
            <p>
                from <span class="fill">{!! $blank($from, 18) !!}</span> to <span class="fill">{!! $blank($to, 18) !!}</span>,
                in partial fulfillment of the
            </p>
            <p>requirements for the degree of <span class="fill">{!! $blank($degree, 48) !!}</span>.</p>
        </div>

        <table class="signatures">
            <tr>
                <td>
                    <div class="signer">{{ $coordinator }}</div>
                    <div class="line">OJT Coordinator</div>
                </td>
                <td>
                    <div class="signer"></div>
                    <div class="line">Program Chair</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
