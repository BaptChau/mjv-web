<?php
namespace App\Exception\Enum;

enum ForumExceptionEnum: int
{
    case POST_NOT_FOUND = 1000;
    case POST_REPORTED  = 1001;
    case POST_REMOVED   = 1002;
}