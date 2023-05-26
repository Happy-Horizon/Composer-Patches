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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function root(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {   
        $args['links'] = [
            'patches' => 'Patches',
            'patches/all' => 'All'
        ];
        return $this->phpRenderer->render($response, 'links.phtml', $args);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function patches(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $args['actions']['/patches/all'] = 'Show All';
        $path = $this->directoryResolver->processPath($args['params'] ?? '');
        return match ($path->getType()) {
            Type::All => $this->allPatches($path, $response, $args),
            Type::Directory => $this->showDirectoryContents($path, $response, $args),
            Type::File => $this->showFile($path, $response, $args),
            Type::Invalid => $this->invalidUrl($path, $response)
        };
    }

    /**
     * @param Path $path
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    protected function allPatches(Path $path, ResponseInterface $response, array $args): ResponseInterface
    {
        $args['links'] = [];
        foreach($this->directoryResolver->getAllPatchFiles($path) as $patchFile) {
            $args['links'][$patchFile] = $patchFile;
        }
        $args['parent'] = $path->getRelativePath();
        return $this->phpRenderer->render($response, 'links.phtml', $args);
    }
    
    /**
     * @param Path $path
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    protected function showDirectoryContents(Path $path, ResponseInterface $response, array $args): ResponseInterface
    {
        $contents = $this->directoryResolver->getDirectoryContents($path);
        
        $args['links'] = [];
        foreach ($contents as $content) {
            if ($content === '.install.flag') {
                $args['actions']['/generate' . $path->getRelativePath()] = 'Generate composer.patches.json';
            } else {
                $args['links'][$path->getRelativePath() . DS . $content] = $content;
            }
        }
        $args['parent'] = $path->getParent();
        
        return $this->phpRenderer->render($response, 'links.phtml', $args);
    }
    
    /**
     * @param Path $path
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    protected function showFile(Path $path, ResponseInterface $response, array $args): ResponseInterface
    {
        $response = $response->withHeader('Content-Type', 'text/plain')->withHeader('Content-Disposition', 'inline');
        $response->getBody()->write(file_get_contents($path->getFullPath()));
        return $response;
    }

    /**
     * @param Path $path
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function invalidUrl(Path $path, ResponseInterface $response): ResponseInterface
    {
        $response->withStatus(404);
        $response->getBody()->write('<span>Invalid Url - <a href="/patches">Back to patch list</a></span>');
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function generate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $path = $this->directoryResolver->processPath($args['params'] ?? '');
        $modules = $this->directoryResolver->getDirectoryContents($path, true);
        $json = [
            'patches' => []
        ];
        $vendor = str_replace('/patches/', '', $path->getParent());
        foreach($modules as $module) {
            $patches = array_diff(scandir($path->getFullPath() . DS . $module), ['.', '..']);
            /**
             * Array keys are needed for cweagans patcher to work
             */
            $i = 0;
            foreach ($patches as $patch) {
                $json['patches'][$vendor . DS . $module]['#' . $i] = "https://patches.experius.nl" . $path->getRelativePath() . DS . $module . DS . $patch;
                $i++;
            }
            
        }
        $response = $response->withHeader('Content-Type', 'text/plain');
        $response->getBody()->write(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $response;
    }
}