<?php

namespace App\Enums;

enum MissionTransportType: string
{
    case PersonalCar = 'personal_car';
    case CompanyCar = 'company_car';
    case Bus = 'bus';
    case Taxi = 'taxi';
    case Flight = 'flight';
    case Other = 'other';
}
