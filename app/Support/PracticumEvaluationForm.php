<?php

namespace App\Support;

class PracticumEvaluationForm
{
    public static function areas(): array
    {
        return [
            'area_1' => [
                'title' => 'AREA 1: COMPETENCE AND DEPENDABILITY',
                'subtotal' => 30,
                'items' => [
                    'quality_standard' => 'Quality of work (ability to meet quality standard)',
                    'satisfactory_output' => 'Quality of work output of satisfactory work',
                    'job_know_how' => 'Job Know-How (Application of acquired knowledge & Skill)',
                    'tools_equipment' => 'Use of tools and equipment',
                    'materials_supplies' => 'Care of materials and supplies',
                    'job_vocabulary' => 'Knowledge of the vocabulary related to the job',
                ],
            ],
            'area_2' => [
                'title' => 'AREA 2: EFFECTIVENESS / ACCURACY & WORK HABITS',
                'subtotal' => 25,
                'items' => [
                    'industry_personal_application' => 'Industry-personal application to assigned work',
                    'reliability' => 'Reliability',
                    'initiative' => 'Initiative',
                    'attendance_punctuality' => 'Attendance and Punctuality',
                    'follow_direction' => 'Ability to follow direction',
                ],
            ],
            'area_3' => [
                'title' => 'AREA 3: INTEREST COOPERATION',
                'subtotal' => 25,
                'items' => [
                    'work_with_people' => 'Ability to work together with other people',
                    'self_control' => 'Ability to control one\'s emotions (self-control)',
                    'self_confidence' => 'Demonstrate level of self-confidence appropriate for the job',
                    'follow_instructions' => 'Willingness to follow directions or instructions',
                    'adjust_to_change' => 'Ability to adjust to new problems and changing situations',
                ],
            ],
            'area_4' => [
                'title' => 'AREA 4: PERSONALITY / INTERPERSONAL RELATIONSHIP',
                'subtotal' => 20,
                'items' => [
                    'criticism' => 'Ability to handle issues constructive criticism',
                    'personality_character' => 'Personality and character',
                    'public_relations' => 'Human and public relations',
                    'grooming' => 'Grooming / Dress Code',
                ],
            ],
        ];
    }

    public static function defaultRatings(int $score = 5): array
    {
        $ratings = [];

        foreach (self::areas() as $areaKey => $area) {
            foreach (array_keys($area['items']) as $itemKey) {
                $ratings[$areaKey][$itemKey] = $score;
            }
        }

        return $ratings;
    }

    public static function total(array $ratings): float
    {
        $total = 0;

        foreach (self::areas() as $areaKey => $area) {
            foreach (array_keys($area['items']) as $itemKey) {
                $total += (int) ($ratings[$areaKey][$itemKey] ?? 0);
            }
        }

        return (float) $total;
    }

    public static function areaTotal(array $ratings, string $areaKey): float
    {
        $total = 0;

        foreach (array_keys(self::areas()[$areaKey]['items'] ?? []) as $itemKey) {
            $total += (int) ($ratings[$areaKey][$itemKey] ?? 0);
        }

        return (float) $total;
    }

    public static function gradeEquivalent(float $rating): string
    {
        return match (true) {
            $rating >= 97 => '1.0',
            $rating >= 94 => '1.25',
            $rating >= 91 => '1.5',
            $rating >= 88 => '1.75',
            $rating >= 85 => '2.0',
            $rating >= 82 => '2.25',
            $rating >= 79 => '2.5',
            $rating >= 76 => '2.75',
            $rating >= 73 => '3.0',
            default => '5.0',
        };
    }
}
