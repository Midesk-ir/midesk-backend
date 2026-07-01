<?php

namespace App\Enums;

enum ReportChartType: string
{
    case Table = 'table';
    case Bar = 'bar';
    case Line = 'line';
    case Pie = 'pie';
    case Area = 'area';
}
