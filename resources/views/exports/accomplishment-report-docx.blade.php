@php
    // Markup mirrors resources/templates/weekly-accomplishment-report.docx (styles, logo rId7 and Table1-5 come from it).
    $run = fn (?string $text, string $rPr = '') => '<w:r><w:rPr>'.$rPr.'</w:rPr><w:t xml:space="preserve">'.e($text).'</w:t></w:r>';
    $lines = fn (array $lines, string $rPr = '') => implode('<w:r><w:br/></w:r>', array_map(fn ($line) => $run($line, $rPr), $lines));
    $small = '<w:sz w:val="18"/><w:szCs w:val="18"/>';
    $bold = '<w:b/><w:bCs/>';
    $field = fn (string $label, $value) => $run($label.': ', $small).(filled($value) ? $run($value, $small.'<w:u w:val="single"/>') : $run('_____________________', $small));
    $signature = fn ($name, string $caption) => (filled($name) ? $run($name, $bold.'<w:u w:val="single"/>') : $run('____________________________')).'<w:r><w:br/></w:r>'.$run($caption);
    $cellBorders = '<w:tcBorders><w:top w:val="single" w:sz="6" w:space="0" w:color="555555"/><w:left w:val="single" w:sz="6" w:space="0" w:color="555555"/><w:bottom w:val="single" w:sz="6" w:space="0" w:color="555555"/><w:right w:val="single" w:sz="6" w:space="0" w:color="555555"/></w:tcBorders>';
    $cell = fn (int $width, string $content, string $extra = '', string $jc = 'left') => '<w:tc><w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/>'.$cellBorders.$extra.'<w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:spacing w:after="0"/><w:jc w:val="'.$jc.'"/></w:pPr>'.$content.'</w:p></w:tc>';
    $pageBreak = '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    $drawingId = 1;
@endphp
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
<w:body>
@foreach ($weeks as $index => $week)
    @if ($index > 0)
        {!! $pageBreak !!}
    @endif

    {{-- Header: logo + school name --}}
    <w:tbl>
        <w:tblPr><w:tblStyle w:val="Table1"/><w:tblW w:w="9792" w:type="dxa"/><w:tblInd w:w="-115" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblLook w:val="0400"/></w:tblPr>
        <w:tblGrid><w:gridCol w:w="1440"/><w:gridCol w:w="8352"/></w:tblGrid>
        <w:tr>
            <w:tc><w:tcPr><w:tcW w:w="1440" w:type="dxa"/></w:tcPr><w:p><w:r><w:drawing><wp:anchor distT="0" distB="0" distL="114300" distR="114300" simplePos="0" relativeHeight="0" behindDoc="0" locked="0" layoutInCell="1" allowOverlap="1"><wp:simplePos x="0" y="0"/><wp:positionH relativeFrom="column"><wp:posOffset>114300</wp:posOffset></wp:positionH><wp:positionV relativeFrom="paragraph"><wp:posOffset>0</wp:posOffset></wp:positionV><wp:extent cx="729615" cy="731520"/><wp:effectExtent l="0" t="0" r="0" b="0"/><wp:wrapNone/><wp:docPr id="{{ $drawingId++ }}" name="Logo"/><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="0" name="image1.png"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="rId7"/><a:srcRect l="1327" r="1327"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="729615" cy="731520"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:anchor></w:drawing></w:r></w:p></w:tc>
            <w:tc><w:tcPr><w:tcW w:w="8352" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="0"/></w:pPr>{!! $run('Republic of the Philippines', '<w:sz w:val="16"/><w:szCs w:val="16"/>') !!}<w:r><w:br/></w:r>{!! $run('EULOGIO “AMANG” RODRIGUEZ', $bold.$small) !!}<w:r><w:br/></w:r>{!! $run('INSTITUTE OF SCIENCE AND TECHNOLOGY', $bold.$small) !!}<w:r><w:br/></w:r>{!! $run('GENERAL MARIANO ALVAREZ, CAVITE', $small) !!}</w:p></w:tc>
        </w:tr>
    </w:tbl>

    <w:p><w:pPr><w:spacing w:before="200" w:after="200"/><w:jc w:val="center"/></w:pPr>{!! $run('WEEKLY ACCOMPLISHMENT FORM', $bold.'<w:sz w:val="28"/><w:szCs w:val="28"/>') !!}</w:p>
    <w:p><w:pPr><w:spacing w:after="120"/><w:jc w:val="center"/></w:pPr>{!! $run('Weekly Report Details', $bold.'<w:sz w:val="20"/><w:szCs w:val="20"/>') !!}</w:p>

    {{-- Weekly report details --}}
    <w:tbl>
        <w:tblPr><w:tblStyle w:val="Table2"/><w:tblW w:w="10080" w:type="dxa"/><w:tblInd w:w="-115" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblLook w:val="0400"/></w:tblPr>
        <w:tblGrid><w:gridCol w:w="4968"/><w:gridCol w:w="5112"/></w:tblGrid>
        @foreach ([['Week', $week['number'], 'Inclusive Dates', $week['dates']], ['Name', $name, 'For the Month of', $week['month']], ['Cooperating Agency', $agency, 'Course/ Year/ Section', $course], ['Department/Office Assigned', $office, 'Address', $address]] as [$leftLabel, $leftValue, $rightLabel, $rightValue])
            <w:tr>
                <w:tc><w:tcPr><w:tcW w:w="4968" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="80"/></w:pPr>{!! $field($leftLabel, $leftValue) !!}</w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="5112" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="80"/></w:pPr>{!! $field($rightLabel, $rightValue) !!}</w:p></w:tc>
            </w:tr>
        @endforeach
    </w:tbl>

    <w:p/>

    {{-- Daily entries: the template has six day rows, extra days add rows --}}
    <w:tbl>
        <w:tblPr><w:tblStyle w:val="Table3"/><w:tblW w:w="10008" w:type="dxa"/><w:tblInd w:w="-108" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblLook w:val="0400"/></w:tblPr>
        <w:tblGrid><w:gridCol w:w="1368"/><w:gridCol w:w="4392"/><w:gridCol w:w="4248"/></w:tblGrid>
        <w:tr><w:trPr><w:tblHeader/></w:trPr>
            @foreach ([1368 => 'DATE/DAY', 4392 => 'WORK ACCOMPLISHED', 4248 => 'SKILLS LEARNED'] as $width => $heading)
                {!! $cell($width, $run($heading, $bold.$small), '<w:shd w:val="clear" w:color="auto" w:fill="E7E7E7"/>', 'center') !!}
            @endforeach
        </w:tr>
        @foreach (array_pad($week['rows'], 6, null) as $row)
            <w:tr><w:trPr><w:cantSplit/><w:trHeight w:val="1030" w:hRule="atLeast"/></w:trPr>
                {!! $cell(1368, $row ? $lines([$row['date'], $row['day']], $small) : '', '', 'center') !!}
                {!! $cell(4392, $row ? $lines($row['work'], $small) : '') !!}
                {!! $cell(4248, $row ? $lines($row['skills'], $small) : '') !!}
            </w:tr>
        @endforeach
    </w:tbl>

    {{-- Signatures --}}
    <w:p><w:pPr><w:spacing w:before="220"/></w:pPr>{!! $run('Prepared by:', $bold) !!}</w:p>
    <w:tbl>
        <w:tblPr><w:tblStyle w:val="Table4"/><w:tblW w:w="9936" w:type="dxa"/><w:tblInd w:w="-115" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblLook w:val="0400"/></w:tblPr>
        <w:tblGrid><w:gridCol w:w="4896"/><w:gridCol w:w="5040"/></w:tblGrid>
        <w:tr>
            <w:tc><w:tcPr><w:tcW w:w="4896" w:type="dxa"/></w:tcPr><w:p/></w:tc>
            <w:tc><w:tcPr><w:tcW w:w="5040" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr>{!! $signature($name, '(Signature of Trainee/Date)') !!}</w:p></w:tc>
        </w:tr>
    </w:tbl>
    <w:p><w:pPr><w:spacing w:before="200"/></w:pPr>{!! $run('Noted by:', $bold) !!}</w:p>
    <w:tbl>
        <w:tblPr><w:tblStyle w:val="Table5"/><w:tblW w:w="10166" w:type="dxa"/><w:tblInd w:w="-115" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblLook w:val="0400"/></w:tblPr>
        <w:tblGrid><w:gridCol w:w="5083"/><w:gridCol w:w="5083"/></w:tblGrid>
        <w:tr>
            <w:tc><w:tcPr><w:tcW w:w="5083" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr>{!! $signature($supervisorName, 'Immediate Supervisor') !!}</w:p></w:tc>
            <w:tc><w:tcPr><w:tcW w:w="5083" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr>{!! $signature($coordinatorName, 'OJT Program Coordinator') !!}</w:p></w:tc>
        </w:tr>
    </w:tbl>

    {{-- Weekly evidence: every photo attached to this week's entries --}}
    @if ($week['evidence'])
        <w:p><w:pPr><w:pageBreakBefore/><w:spacing w:after="200"/><w:jc w:val="center"/></w:pPr>{!! $run('WEEKLY EVIDENCE', $bold.'<w:sz w:val="28"/><w:szCs w:val="28"/>') !!}</w:p>
        <w:p><w:pPr><w:spacing w:after="200"/><w:jc w:val="center"/></w:pPr>{!! $run('Week '.$week['number'].' ('.$week['dates'].')', $small) !!}</w:p>
        @foreach ($week['evidence'] as $day)
            <w:p><w:pPr><w:keepNext/><w:spacing w:before="120" w:after="60"/></w:pPr>{!! $run($day['label'], $bold.$small) !!}</w:p>
            <w:p><w:pPr><w:jc w:val="center"/></w:pPr>
                @foreach ($day['photos'] as $photo)
                    <w:r><w:drawing><wp:inline distT="45720" distB="45720" distL="45720" distR="45720"><wp:extent cx="{{ $photo['cx'] }}" cy="{{ $photo['cy'] }}"/><wp:docPr id="{{ $drawingId++ }}" name="Evidence {{ $photo['index'] }}"/><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="0" name="photo{{ $photo['index'] }}.{{ $photo['ext'] }}"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="rIdPhoto{{ $photo['index'] }}"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="{{ $photo['cx'] }}" cy="{{ $photo['cy'] }}"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r>{!! $run(' ') !!}
                @endforeach
            </w:p>
        @endforeach
    @endif
@endforeach
<w:sectPr>
    <w:pgSz w:w="12240" w:h="15840"/>
    <w:pgMar w:top="893" w:right="1037" w:bottom="893" w:left="1037" w:header="720" w:footer="720" w:gutter="0"/>
</w:sectPr>
</w:body>
</w:document>
