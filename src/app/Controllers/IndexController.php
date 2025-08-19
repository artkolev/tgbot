<?php

declare(strict_types=1);

namespace TGBot\Controllers;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class IndexController
 */
class IndexController extends BaseController
{
    /**
     * Return index page (/)
     * @return Response
     */
    public function get(): Response
    {
        return new Response('ArtKolev TGBot API v1.0');
    }
}
