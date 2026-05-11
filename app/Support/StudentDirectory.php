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
        ['student_id' => '2316-02100C', 'full_name' => 'ABAS, RODANIELYN ERINCATH M.', 'contact_number' => '09174286013', 'email' => 'abas2100@gmail.com'],
        ['student_id' => '2316-02096C', 'full_name' => 'ABDULA, DENNIA ELEZAR G.', 'contact_number' => '09265830741', 'email' => 'abdula2096@gmail.com'],
        ['student_id' => '2316-02171C', 'full_name' => 'ALTAMIA, ROSS IRVINE T.', 'contact_number' => '09358617402', 'email' => 'altamia2171@gmail.com'],
        ['student_id' => '2316-02107C', 'full_name' => 'BABAC, ABELARDO P.', 'contact_number' => '09473168250', 'email' => 'babac2107@gmail.com'],
        ['student_id' => '2316-02159C', 'full_name' => 'BACOLOD, JAMES KYLE B.', 'contact_number' => '09584726031', 'email' => 'bacolod2159@gmail.com'],
        ['student_id' => '2316-02106C', 'full_name' => 'BANAGAN, JORGE JERIX I.', 'contact_number' => '09635298174', 'email' => 'banagan2106@gmail.com'],
        ['student_id' => '2316-02102C', 'full_name' => 'BAYUGO, JERALD B.', 'contact_number' => '09746183052', 'email' => 'bayugo2102@gmail.com'],
        ['student_id' => '2316-02123C', 'full_name' => 'BEA, CARL TRISTAN G.', 'contact_number' => '09853721640', 'email' => 'bea2123@gmail.com'],
        ['student_id' => '2216-00377C', 'full_name' => 'BONTIA, MARU ANDREAS', 'contact_number' => '09962837415', 'email' => 'bontia0377@gmail.com'],
        ['student_id' => '2316-02134C', 'full_name' => 'BOYLES, ADRIANNE RAFAEL F.', 'contact_number' => '09075621843', 'email' => 'boyles2134@gmail.com'],
        ['student_id' => '2316-02097C', 'full_name' => 'BREGONIA, JHULMAR B.', 'contact_number' => '09186347520', 'email' => 'bregonia2097@gmail.com'],
        ['student_id' => '2316-021043C', 'full_name' => 'BRINGAS, JHONABEL D.', 'contact_number' => '09297463185', 'email' => 'bringas21043@gmail.com'],
        ['student_id' => '2316-02104C', 'full_name' => 'BRINGAS, JUNLY D.', 'contact_number' => '09318574620', 'email' => 'bringas2104@gmail.com'],
        ['student_id' => '2316-02159C', 'full_name' => 'CABILES, DANILA', 'contact_number' => '09429685731', 'email' => 'cabiles2159@gmail.com'],
        ['student_id' => '2316-02186C', 'full_name' => 'CABUAN, IRISH DANICA C.', 'contact_number' => '09530746812', 'email' => 'cabuan2186@gmail.com'],
        ['student_id' => '2316-02181C', 'full_name' => 'CEDRO, DINDO I M.', 'contact_number' => '09641857923', 'email' => 'cedro2181@gmail.com'],
        ['student_id' => '2216-00369C', 'full_name' => 'CUPAG, JOSEPH S.', 'contact_number' => '09752968034', 'email' => 'cupag0369@gmail.com'],
        ['student_id' => '2316-02173C', 'full_name' => 'DE RAMOS, GABRIEL JOSEPH N.', 'contact_number' => '09863079145', 'email' => 'deramos2173@gmail.com'],
        ['student_id' => '2316-02175C', 'full_name' => 'ESTOSANE, KENZO T.', 'contact_number' => '09974180256', 'email' => 'estosane2175@gmail.com'],
        ['student_id' => '2316-02140C', 'full_name' => 'FERMO, MICHAEL M.', 'contact_number' => '09085291367', 'email' => 'fermo2140@gmail.com'],
        ['student_id' => '2316-02185C', 'full_name' => 'FERNANDEZ, PRINCE JOHN ALBERT C.', 'contact_number' => '09196302478', 'email' => 'fernandez2185@gmail.com'],
        ['student_id' => '2316-02137C', 'full_name' => 'FORMARAN, JAMES JOHN E.', 'contact_number' => '09217413589', 'email' => 'formaran2137@gmail.com'],
        ['student_id' => '2316-02174C', 'full_name' => 'GOREMBALEM, ROBY JAY B.', 'contact_number' => '09328524690', 'email' => 'gorembalem2174@gmail.com'],
        ['student_id' => '2316-02180C', 'full_name' => 'GUZON, JOHN BENEDICT S.', 'contact_number' => '09439635701', 'email' => 'guzon2180@gmail.com'],
        ['student_id' => '2316-02169C', 'full_name' => 'HARINA, KYLA ARABELLA D.', 'contact_number' => '09540746812', 'email' => 'harina2169@gmail.com'],
        ['student_id' => '2316-02132C', 'full_name' => 'HERNANDEZ, JHON CHRISTIAN ARVIS C.', 'contact_number' => '09651857923', 'email' => 'hernandez2132@gmail.com'],
        ['student_id' => '2316-02165C', 'full_name' => 'IGNACIO, RAUL ERICK B.', 'contact_number' => '09762968034', 'email' => 'ignacio2165@gmail.com'],
        ['student_id' => '2316-02113C', 'full_name' => 'JARDINO, KRISTINE M.', 'contact_number' => '09873079145', 'email' => 'jardino2113@gmail.com'],
        ['student_id' => '2316-02750C', 'full_name' => 'LIMUCO, RONPAUL R.', 'contact_number' => '09984180256', 'email' => 'limuco2750@gmail.com'],
        ['student_id' => '2316-02114C', 'full_name' => 'LUCBAN, JEROME VINCENT C.', 'contact_number' => '09095291367', 'email' => 'lucban2114@gmail.com'],
        ['student_id' => '2316-02545C', 'full_name' => 'MALLARI, JAYCEE E.', 'contact_number' => '09106402478', 'email' => 'mallari2545@gmail.com'],
        ['student_id' => '2316-02111C', 'full_name' => 'MONTAJES, JEAN ROSE C.', 'contact_number' => '09217513589', 'email' => 'montajes2111@gmail.com'],
        ['student_id' => '2316-02128C', 'full_name' => 'OLIP, JAHZREL', 'contact_number' => '09328624690', 'email' => 'olip2128@gmail.com'],
        ['student_id' => '2316-02127C', 'full_name' => 'PANINGBATAN, NOEMI S.', 'contact_number' => '09439735701', 'email' => 'paningbatan2127@gmail.com'],
        ['student_id' => '2316-02138C', 'full_name' => 'PASIGNA, NICOLE R.', 'contact_number' => '09540846812', 'email' => 'pasigna2138@gmail.com'],
        ['student_id' => '2316-02125C', 'full_name' => 'RAMIREZ, ALEXA V.', 'contact_number' => '09651957923', 'email' => 'ramirez2125@gmail.com'],
        ['student_id' => '2316-02168C', 'full_name' => 'RAMOS, MARK ANTHONY N.', 'contact_number' => '09762068034', 'email' => 'ramos2168@gmail.com'],
        ['student_id' => '2316-02116C', 'full_name' => 'RARAS, RHON CEDRICK A.', 'contact_number' => '09873179145', 'email' => 'raras2116@gmail.com'],
        ['student_id' => '2316-02099C', 'full_name' => 'REYES, LADY LEE C.', 'contact_number' => '09984280256', 'email' => 'reyes2099@gmail.com'],
        ['student_id' => '2316-0217C', 'full_name' => 'RIVAS, ELLA MAE T.', 'contact_number' => '09095391367', 'email' => 'rivas2170@gmail.com'],
        ['student_id' => '2316-02178C', 'full_name' => 'RIVERA, JULIUS M.', 'contact_number' => '09106502478', 'email' => 'rivera2178@gmail.com'],
        ['student_id' => '2316-02121C', 'full_name' => 'ROYO, RENDEL REN R.', 'contact_number' => '09217613589', 'email' => 'royo2121@gmail.com'],
        ['student_id' => '2316-02110C', 'full_name' => 'SABILLO, WAREN I.', 'contact_number' => '09328724690', 'email' => 'sabillo2110@gmail.com'],
        ['student_id' => '2316-02101C', 'full_name' => 'SIGNAR, GLESEL', 'contact_number' => '09439835701', 'email' => 'signar2101@gmail.com'],
        ['student_id' => '2316-02130C', 'full_name' => 'TOLENTINO, JIMWEL A.', 'contact_number' => '09540946812', 'email' => 'tolentino2130@gmail.com'],
        ['student_id' => '2316-02119C', 'full_name' => 'VALENCIA, JEFFERSON I.', 'contact_number' => '09651057923', 'email' => 'valencia2119@gmail.com'],
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
                'email' => $student['email'] ?? self::emailFromLastName($name['last_name']),
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
