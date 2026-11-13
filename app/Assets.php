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
        $relativePath = str_replace('\\', '/', (string)($args['filepath'] ?? ''));
        $relativePath = ltrim($relativePath, '/');
        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return $response->withStatus(404);
        }

        $baseDir = realpath(BP . '/view/static');
        $filePath = $baseDir !== false ? realpath($baseDir . '/' . $relativePath) : false;

        if ($baseDir === false || $filePath === false || !str_starts_with($filePath, $baseDir . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
            return $response->withStatus(404);
        }

        $mimeType = match (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
            'ico' => 'image/x-icon',
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        $staticResponse = $response->withHeader('Content-Type', $mimeType);
        $staticResponse->getBody()->write((string)file_get_contents($filePath));

        return $staticResponse;
    }
}