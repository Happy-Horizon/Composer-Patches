<?php

namespace HH\Patches;

use HH\Patches\Data\Enums\Type;
use HH\Patches\Data\Path;
use HH\Patches\Utils;
use League\CommonMark\CommonMarkConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;

class Controller
{
    /**
     * @var PhpRenderer 
     */
    protected PhpRenderer $phpRenderer;

    /**
     * @param DirectoryResolver $directoryResolver
     * @param CommonMarkConverter $markConverter
     */
    public function __construct(
        protected DirectoryResolver $directoryResolver,
        protected CommonMarkConverter $markConverter,
        protected Utils $utils
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
            'patches/all' => 'All',
            'patches/readme' => 'README'
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
        $args['actions']['/patches/readme'] = 'README';
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
        $patches = $this->directoryResolver->getAllPatchFiles($path);
        usort($patches, function ($patch1, $patch2) {
            return $this->utils->getPatchNumber($patch1) - $this->utils->getPatchNumber($patch2);
        });
        foreach($patches as $patchFile) {
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
            $args['links'][$path->getRelativePath() . DS . $content] = $content;
        }
        if ($this->directoryResolver->getVendor($path)) {
            $args['actions']['/generate' . $path->getRelativePath()] = 'Generate composer.patches.json';
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
        if ($path->getExtension() === 'md') {
            $content = $this->markConverter->convert($path->getContents())->getContent();
            $response = $response->withHeader('Content-Type', 'text/html')->withHeader('Content-Disposition', 'inline');
        } else {
            $content = $path->getContents();
            $response = $response->withHeader('Content-Type', 'text/plain')->withHeader('Content-Disposition', 'inline');
        }
        $response->getBody()->write($content);
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
        $modules = $this->directoryResolver->getDirectoryContents($path);
        $json = [
            'patches' => []
        ];
        $vendor = $this->directoryResolver->getVendor($path);
        foreach($modules as $module) {
            $patches = array_diff(scandir($path->getFullPath() . DS . $module), ['.', '..']);
            /**
             * Array keys are needed for cweagans patcher to work
             */
            $i = 0;
            foreach ($patches as $patch) {
                $json['patches'][$vendor . DS . $module][$this->utils->getPatchNumber($patch, $i)] = "https://patches.experius.nl" . $path->getRelativePath() . DS . $module . DS . $patch;
                $i++;
            }
            
        }
        $response = $response->withHeader('Content-Type', 'text/plain');
        $response->getBody()->write(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $response;
    }
}