<?php

namespace App\Controller;

use App\Enum\ViewMode;
use App\ProjectTree\File;
use App\ProjectTree\ProjectTreeLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FileController extends AbstractController
{
    // The negative priority makes this catch-all the last route tried, after /admin and Symfony's own routes.
    #[Route(
        '/{path}',
        name: 'app_file',
        requirements: ['path' => '.+'],
        defaults: ['path' => 'README.md'],
        methods: ['GET'],
        priority: -1,
    )]
    public function show(string $path, Request $request, ProjectTreeLoader $loader): Response
    {
        $root = $loader->load();

        if ($root === null) {
            return $this->render(
                'empty_project.html.twig',
                response: new Response(status: Response::HTTP_SERVICE_UNAVAILABLE),
            );
        }

        $node = $root->find($path);
        // find() resolves paths like a shell, but only the exact path of a file is a page.
        $file = $node instanceof File && $node->path === $path ? $node : null;
        $urlViewMode = ViewMode::tryFrom($request->query->getString('view'));

        return $this->render(
            'file.html.twig',
            [
                'root' => $root,
                'file' => $file,
                'requestedPath' => $path,
                'viewMode' => $urlViewMode ?? ($path === 'README.md' ? ViewMode::Preview : ViewMode::Code),
                'viewModeInUrl' => $urlViewMode !== null,
            ],
            new Response(status: $file === null ? Response::HTTP_NOT_FOUND : Response::HTTP_OK),
        );
    }
}
