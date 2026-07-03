<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\LinkService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP entry point for shortening URLs.
 */
final class ShortLinkController
{
    public function __construct(
        private readonly LinkService $linkService,
    ) {
    }

    /**
     * Shortens the given URL or returns an existing short code.
     */
    #[Route('/api/shortlink', name: 'api_shortlink', methods: ['GET'])]
    public function shortlink(Request $request): JsonResponse
    {
        $url = $request->query->get('url');

        if (!is_string($url) || trim($url) === '') {
            return new JsonResponse(
                ['error' => 'The "url" query parameter is required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $link = $this->linkService->shortenOrFetch($url);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST,
            );
        }

        if ($link->isReady()) {
            return new JsonResponse(
                [
                    'status' => 'ready',
                    'original_url' => $link->getOriginalUrl(),
                    'short_code' => $link->getShortCode(),
                ],
                Response::HTTP_OK,
            );
        }

        return new JsonResponse(
            [
                'status' => 'processing',
                'message' => 'The short link is being generated. Please retry shortly.',
            ],
            Response::HTTP_ACCEPTED,
        );
    }

    /**
     * Resolves a short code and redirects to its original URL.
     */
    #[Route('/{code}', name: 'shortlink_redirect', methods: ['GET'], requirements: ['code' => '[a-zA-Z0-9]{4,8}'])]
    public function redirect(string $code): Response
    {
        $link = $this->linkService->findReadyByCode($code);

        if ($link === null) {
            return new JsonResponse(
                ['error' => 'Short link not found.'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return new RedirectResponse($link->getOriginalUrl(), Response::HTTP_MOVED_PERMANENTLY);
    }
}
