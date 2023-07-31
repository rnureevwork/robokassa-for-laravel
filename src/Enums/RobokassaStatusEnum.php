<?php

namespace Icekristal\RobokassaForLaravel\Enums;

enum RobokassaStatusEnum: int
{
    case NEW = 0;
    case WAITING = 10;
    case PAID = 50;
    case CANCEL = 40;

}
