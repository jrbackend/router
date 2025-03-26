<?php

namespace Source;

/**
 * Https Status Code
 */
enum HttpStatusCode: int
{
    case BadRequest = 400;
    case NotFound = 404;
    case MethodNotAllowed = 405;
    case NotImplemented = 501;

    public const int BAD_REQUEST = self::BadRequest->value;

    public const int NOT_FOUND = self::NotFound->value;

    public const int METHOD_NOT_ALLOWED = self::MethodNotAllowed->value;

    public const int NOT_IMPLEMENTED = self::NotImplemented->value;
}
