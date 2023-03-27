<?php

declare(strict_types=1);

namespace AUS\ReduceDuplicateContent\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class ReduceDuplicateContentMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly UriBuilder $uriBuilder)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $language = $request->getAttribute('language');
        $page = $request->getAttribute('routing');

        if (!$language instanceof SiteLanguage) {
            return $handler->handle($request);
        }

        if (!$page instanceof PageArguments) {
            return $handler->handle($request);
        }

        $canonicalUri = new Uri(
            $this->uriBuilder
                ->setTargetPageUid($page->getPageId())
                ->setLanguage((string)$language->getLanguageId())
                ->setCreateAbsoluteUri(true)
                ->buildFrontendUri()
        );

        if (rtrim((string)$canonicalUri, '/') !== rtrim((string)$request->getUri(), '/')) {
            return $handler->handle($request);
        }

        if ((string)$canonicalUri === (string)$request->getUri()) {
            return $handler->handle($request);
        }

        return new RedirectResponse($canonicalUri, $this->getStatusCode(), [
            'X-Redirect-Reason' => self::class,
        ]);
    }

    private function getStatusCode(): int
    {
        try {
            return (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('reduce_duplicate_content', 'statusCode');
        } catch (\Exception) {
            // do nothing
        }

        return 307;
    }
}
