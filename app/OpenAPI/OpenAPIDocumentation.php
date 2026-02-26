<?php

namespace App\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        title: 'ScholarsToday IELTS API',
        description: 'Dokumentasi API',
        version: '1.0.0',
        contact: new OA\Contact(email: 'kevinmajesta@example.com')
    ),
    servers: [
        new OA\Server(url: 'http://localhost:8000', description: 'Development Server'),
    ]
)]
#[OA\SecurityScheme(
    type: 'http',
    description: 'Bearer token authentication',
    name: 'Token',
    in: 'header',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    securityScheme: 'bearerAuth'
)]
class OpenApiDocumentation {}
