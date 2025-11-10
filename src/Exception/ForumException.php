<?php
namespace App\Exception;

use App\Enum\ForumExceptionEnum;
use Exception;

final class ForumException extends Exception
{
    private function __construct(string $message, int $code, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function errorFactory(int $code): ForumException
    {
        return match($code) {
            ForumExceptionEnum::POST_NOT_FOUND => new ForumException('Post introuvable', $code),
            ForumExceptionEnum::POST_REMOVED => new ForumException('Post retiré du forum', $code),
            ForumExceptionEnum::POST_REPORTED => new ForumException('Post signalié à la modération', $code)
        };
    }
}