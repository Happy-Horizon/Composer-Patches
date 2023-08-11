<?php

namespace HappyHorizon\Patches;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Assets
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function static(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $filePath = BP . '/view/static/' . $args['filepath'] ?? '';

        if (!file_exists($filePath)) {
            return $response->withStatus(404, 'File Not Found');
        }

        $mimeType = match (pathinfo($filePath, PATHINFO_EXTENSION)) {
            'ico' => 'image/x-icon',
            default => 'text/html; charset=UTF-8',
        };

        $staticResponse = $response->withHeader('Content-Type', $mimeType );
        $staticResponse->getBody()->write(file_get_contents($filePath));

        return $staticResponse;
    }
}