<?php

namespace enums;

enum StatusSendEnum: int
{
    case NO_SEND = 0;
    case PROGRESS = 1;
    case SEND = 2;
    case FAILED = 3;
}