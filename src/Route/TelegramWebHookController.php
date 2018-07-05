<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Route;

use React\Http\Response;

class TelegramWebHookController
{
    /**
     * @return Response
     */
    public function handle(): Response
    {
        return new Response(200, [], 'Hello world');
    }
}