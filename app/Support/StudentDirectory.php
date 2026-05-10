<?php

namespace App\Support;

class StudentDirectory
{
    private const STUDENTS = [
        ['student_id' => '2316-02353C', 'full_name' => 'ABORDO, JOHN CLARK GERMONES', 'contact_number' => '09815712041'],
        ['student_id' => '2316-02354C', 'full_name' => 'ADARLE, LOUIE JOHN INION', 'contact_number' => '09811473980'],
        ['student_id' => '2316-02356C', 'full_name' => 'ALEJANDRO, KENSHIN JHEMY AGAO', 'contact_number' => '09945952585'],
        ['student_id' => '2316-02357C', 'full_name' => 'BACOS, JOHN LLOYD ALONDE', 'contact_number' => '09949462101'],
        ['student_id' => '2316-02358C', 'full_name' => 'BANATLAO, KHIAN NIXON LEAL', 'contact_number' => '09123210741'],
        ['student_id' => '2316-02359C', 'full_name' => 'BANTUGAN, DANIELLA SACLAUSA', 'contact_number' => '09770940604'],
        ['student_id' => '2316-02360C', 'full_name' => 'BELBIDER, MARTIN BUSTAMANTE', 'contact_number' => '09507256428'],
        ['student_id' => '2316-02578C', 'full_name' => 'BELLEZA, JOSHUA MIRAFLOR', 'contact_number' => '09218869520'],
        ['student_id' => '2316-02361C', 'full_name' => 'BENITUA, JOHN BRIX CAMAYANG', 'contact_number' => '09092402216'],
        ['student_id' => '2316-02421C', 'full_name' => 'BOISER, REYNANTE TABANGAY', 'contact_number' => '09655182673'],
        ['student_id' => '2316-02362C', 'full_name' => 'CARIAGA, SHAN CHESTER CAPUYAN', 'contact_number' => '09763264199'],
        ['student_id' => '2316-02364C', 'full_name' => 'CRUZ, JONALYN DE LEON', 'contact_number' => '09668825287'],
        ['student_id' => '2316-02365C', 'full_name' => 'DAG-UMAN, RHON NIEL NAVAROSA', 'contact_number' => '0949964685'],
        ['student_id' => '2316-02366C', 'full_name' => 'DATILES, SHANLEY FAYE TUMAMBAC', 'contact_number' => '09303783524'],
        ['student_id' => '2316-02367C', 'full_name' => 'DE LARA, WALTER JAKE BALLESTEROS', 'contact_number' => '09127766560'],
        ['student_id' => '2316-02368C', 'full_name' => 'DELA CRUZ JR, PATRICIO FONACIER', 'contact_number' => '09151138764'],
        ['student_id' => '2316-02369C', 'full_name' => 'EMBATE, DUSTINE KIM TADENA', 'contact_number' => '09128469778'],
        ['student_id' => '2316-02642C', 'full_name' => 'ENTONA, ALTHEA YESHA PEREZ', 'contact_number' => '09958460661'],
        ['student_id' => '2316-02370C', 'full_name' => 'FENNIS, SHAIRA NICOLE FRANCO', 'contact_number' => '09858910414'],
        ['student_id' => '2316-02371C', 'full_name' => 'FORTALEZA, JUSTINE MARC OLEA', 'contact_number' => '09109497203'],
        ['student_id' => '2316-02372C', 'full_name' => 'JACALA, JUSTIN ARON GALICIA', 'contact_number' => '09264083150'],
        ['student_id' => '2316-02373C', 'full_name' => 'LABRAGUE, ALLEN INGRAHAM', 'contact_number' => '09156862513'],
        ['student_id' => '2316-02374C', 'full_name' => 'LEE, TEY HON POTES', 'contact_number' => '09267373708'],
        ['student_id' => '2316-02376C', 'full_name' => 'LLANERA, MARK JIMS ALVAREZ', 'contact_number' => '09812583840'],
        ['student_id' => '2316-02627C', 'full_name' => 'MACEDA, JOHN ROBERT VICENTE', 'contact_number' => '09269528561'],
        ['student_id' => '2316-02378C', 'full_name' => 'MALILAY, DREXEL B', 'contact_number' => '09943910686'],
        ['student_id' => '2316-02379C', 'full_name' => 'MANES, GINEROSE RAMIREZ', 'contact_number' => '09919304319'],
        ['student_id' => '2316-02363C', 'full_name' => 'MANZANO, FAIZAL CALIPATA', 'contact_number' => '09391914086'],
        ['student_id' => '2316-02381C', 'full_name' => 'PALERMO, GRANT RABANG', 'contact_number' => '09365337323'],
        ['student_id' => '2316-02382C', 'full_name' => 'PETIPIT, JOMARI LIBERTY', 'contact_number' => '09764396224'],
        ['student_id' => '2316-02383C', 'full_name' => 'RAZONABLE, ROBERTO REGUAL', 'contact_number' => '09952137302'],
        ['student_id' => '2316-02384C', 'full_name' => 'REGALADO, FRANCINE REXIA GACER', 'contact_number' => '09608748228'],
        ['student_id' => '2316-02385C', 'full_name' => 'ROM, KURT JOHN VILLASIN', 'contact_number' => '09703906427'],
        ['student_id' => '2316-02386C', 'full_name' => 'ROMANO, REYMART PALO', 'contact_number' => '09666675924'],
        ['student_id' => '2316-02388C', 'full_name' => 'SACAPANIO, KYLE', 'contact_number' => '09709379081'],
        ['student_id' => '2316-02391C', 'full_name' => 'SUBICO, JUDY ANN LERAOG', 'contact_number' => '09453156437'],
        ['student_id' => '2316-02393C', 'full_name' => 'TING, JOHN MERCO', 'contact_number' => '09309213164'],
        ['student_id' => '2316-02394C', 'full_name' => 'VILLAMOR, JEXTER MOLLANEDA', 'contact_number' => '09078502229'],
        ['student_id' => '2316-02394C', 'full_name' => 'VILLANUEVA AUGOSTO VINCE BORJA', 'contact_number' => '09686387080'],
    ];

    public static function find(string $studentId): ?array
    {
        $studentId = strtoupper(trim($studentId));

        foreach (self::STUDENTS as $student) {
            if ($student['student_id'] !== $studentId) {
                continue;
            }

            $name = self::splitName($student['full_name']);

            return [
                ...$name,
                'student_id' => $student['student_id'],
                'email' => self::emailFromLastName($name['last_name']),
                'contact_number' => $student['contact_number'],
            ];
        }

        return null;
    }

    private static function splitName(string $fullName): array
    {
        if (str_contains($fullName, ',')) {
            [$lastName, $givenNames] = array_map('trim', explode(',', $fullName, 2));
        } else {
            $parts = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $lastName = array_shift($parts) ?? '';
            $givenNames = implode(' ', $parts);
        }

        $givenParts = preg_split('/\s+/', trim($givenNames), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstName = array_shift($givenParts) ?? '';

        return [
            'first_name' => $firstName,
            'middle_name' => implode(' ', $givenParts),
            'last_name' => $lastName,
        ];
    }

    private static function emailFromLastName(string $lastName): string
    {
        $localPart = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $lastName));

        return $localPart.'@gmail.com';
    }
}
