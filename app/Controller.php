<?php

namespace HH\Patches;

use HH\Patches\Data\Enums\Type;
use HH\Patches\Data\Path;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;

class Controller
{
    /**
     * @var PhpRenderer 
     */
    protected PhpRenderer $phpRenderer;
    public function __construct(
        protected DirectoryResolver $directoryResolver
    ) {
        $this->phpRenderer = new PhpRenderer(__DIR__ . '/../view/templates/');
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function root(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {   
        $args['links'] = [
            'patches' => 'Patches'
        ];
        return $this->phpRenderer->render($response, 'links.phtml', $args);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function patches(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $path = $this->directoryResolver->processPath($args['params'] ?? '');
        switch ($path->getType()) {
            case Type::Directory:
                $response = $this->showDirectoryContents($path, $response, $args);
                break;
            case Type::File:
                $response = $this->showFile($path, $response, $args);
                break;
            case Type::Invalid:
                $response = $this->invalidUrl($path, $response);
                break;
        }
        return $response;
    }
    
    protected function showDirectoryContents(Path $path, ResponseInterface $response, array $args): ResponseInterface
    {
        $contents = array_diff(scandir($path->getFullPath()), ['.', '..']);
        
        foreach ($contents as $content) {
            $args['links'][$path->getRelativePath() . '/' . $content] = ucfirst($content);
        }
        $args['parent'] = $path->getParent();
        
        return $this->phpRenderer->render($response, 'links.phtml', $args);
    }
    
    protected function showFile(Path $path, ResponseInterface $response, array $args): ResponseInterface
    {
        $response = $response->withHeader('Content-Type', 'text/x-diff');
        $response->getBody()->write(file_get_contents($path->getFullPath()));
        return $response;
    }
    
    protected function invalidUrl($path, ResponseInterface $response): ResponseInterface
    {
        $response->withStatus(404);
        $response->getBody()->write('Invalid Url');
        return $response;
    }
}