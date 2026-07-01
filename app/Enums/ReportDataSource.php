<?php

namespace App\Enums;

enum ReportDataSource: string
{
    case Tasks = 'tasks';
    case Projects = 'projects';
    case Attendance = 'attendance';
    case Leave = 'leave';
    case Expenses = 'expenses';
    case Payroll = 'payroll';
    case Personnel = 'personnel';
    case Worklogs = 'worklogs';
}
