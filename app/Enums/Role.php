<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case OJTCOORDINATOR = 'ojt_coordinator';
    case COMPANYSUPERVISOR = 'company_supervisor';
    case STUDENT = 'student';
}
