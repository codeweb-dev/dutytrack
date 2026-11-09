{{-- Certification statement with the signature over the complete name, bottom-right. --}}
@if ($certification)
    <div class="sign">
        <div class="certification">{{ $certification }}</div>

        <div class="sign-box">
            <table width="100%">
                <tr>
                    <td class="sign-img">
                        @if ($signature)
                            <img src="{{ $signature['src'] }}" alt="" style="{{ \App\Support\ResumeDocument::fit($signature, 190, 44) }}">
                        @endif
                    </td>
                </tr>
            </table>
            <div class="sign-line">{{ $name ?: 'Complete Name' }}</div>
        </div>
    </div>
@endif
