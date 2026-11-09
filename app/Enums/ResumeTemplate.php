<?php

namespace App\Enums;

enum ResumeTemplate: string
{
    case Earist = 'earist';
    case Modern = 'modern';
    case Minimalist = 'minimalist';
    case Corporate = 'corporate';

    public function label(): string
    {
        return match ($this) {
            self::Earist => 'EARIST Classic',
            self::Modern => 'Modern Professional',
            self::Minimalist => 'Minimalist',
            self::Corporate => 'Corporate',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Earist => 'Formal bordered layout from the EARIST OJT workbook.',
            self::Modern => 'Accent header with skills side by side.',
            self::Minimalist => 'Spacious monochrome layout, ATS-friendly.',
            self::Corporate => 'Business-ready with strong section hierarchy.',
        };
    }

    public function view(): string
    {
        return 'exports.resumes.'.$this->value;
    }
}
