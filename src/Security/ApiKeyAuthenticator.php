<?php

declare(strict_types=1);

namespace GeoProxy\Security;

final class ApiKeyAuthenticator
{
    public function isHeaderPresent(?string $authorization): bool
    {
        return is_string($authorization) && str_starts_with($authorization, 'Bearer gp_');
    }
}
