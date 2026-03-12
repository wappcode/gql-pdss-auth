<?php

namespace GPDAuth\Enums;

enum AuthenticatedUserType: string
{
    case API_CLIENT = 'api_client';
    case LOCAL_USER = 'local_user';
    case EXTERN_USER = 'extern_user';
}
