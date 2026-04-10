<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case OJTCOORDINATOR = 'ojt_coordinator';
    case PROFESSOR = 'professor';
    case COMPANYSUPERVISOR = 'company_supervisor';
    case STUDENT = 'student';
}
