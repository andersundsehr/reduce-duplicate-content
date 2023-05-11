<?php

declare(strict_types=1);

namespace AUS\ReduceDuplicateContent\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Routing\PageRouter;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class ReduceDuplicateContentMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ExtensionConfiguration $extensionConfiguration, private readonly PageRepository $pageRepository)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        $language = $request->getAttribute('language');
        $pageArguments = $request->getAttribute('routing');
        $normalizedParams = $request->getAttribute('normalizedParams');

        if (!$language instanceof SiteLanguage) {
            return $handler->handle($request);
        }

        if (!$pageArguments instanceof PageArguments) {
            return $handler->handle($request);
        }

        if (!$site instanceof Site) {
            return $handler->handle($request);
        }

        if (!$normalizedParams instanceof NormalizedParams) {
            return $handler->handle($request);
        }

        if (!in_array($request->getMethod(), ['GET', 'HEAD'])) {
            return $handler->handle($request);
        }

        //if page hase parameters other than type, language and id:
        if (new PageArguments($pageArguments->getPageId(), $pageArguments->getPageType(), []) != $pageArguments) {
            return $handler->handle($request);
        }

        // if page is in preview mode it can not be found in the pageRepository:
        $page = $this->pageRepository->getPage($pageArguments->getPageId(), true);
        if (!$page) {
            return $handler->handle($request);
        }

        try {
            $parameter = array_filter(
                [
                    ...$pageArguments->getQueryArguments(),
                    'type' => $pageArguments->getPageType(),
                    '_language' => $language,
                ]
            );
            $canonicalUri = $site->getRouter()->generateUri($page, $parameter);
        } catch (InvalidRouteArgumentsException) {
            return $handler->handle($request);
        }

        if ($canonicalUri->getPath() === $request->getUri()->getPath()) {
            return $handler->handle($request);
        }

        return new RedirectResponse($canonicalUri, $this->getStatusCode(), [
            'X-Redirect-Reason' => self::class,
        ]);
    }

    private function getStatusCode(): int
    {
        return (int)$this->extensionConfiguration->get('reduce_duplicate_content', 'statusCode');
    }

    protected function getUrlEnd(Site $site, Uri $uri): string
    {
        $routeEnhancers = $site->getConfiguration()['routeEnhancers'] ?? [];
        foreach ($routeEnhancers as $enhancer) {
            if ($enhancer['type'] !== 'PageType') {
                continue;
            }

            return $enhancer['default'];
        }

        return '';
    }
}
