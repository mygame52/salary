<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeSubdirectoryUrls
{
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        $basePath = (string) parse_url($appUrl, PHP_URL_PATH);

        // If an incoming request arrives with the subdirectory prefix still attached (e.g. /salary/dashboard),
        // strip it so Laravel's router matches the route cleanly instead of returning 404.
        if ($basePath && $basePath !== '/') {
            $reqUri = (string) $request->server->get('REQUEST_URI', '');
            if ($reqUri === $basePath || str_starts_with($reqUri, $basePath . '/') || str_starts_with($reqUri, $basePath . '?')) {
                $strippedUri = substr($reqUri, strlen($basePath));
                if ($strippedUri === '' || str_starts_with($strippedUri, '?')) {
                    $strippedUri = '/' . $strippedUri;
                }
                $request->server->set('REQUEST_URI', $strippedUri);
                $request->initialize(
                    $request->query->all(),
                    $request->request->all(),
                    $request->attributes->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all(),
                    $request->getContent()
                );
            }
        }

        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $normalizeUrl = function (?string $url) use ($appUrl, $basePath): ?string {
            if (!$url || !$appUrl || !$basePath || $basePath === '/') {
                return $url;
            }

            if (preg_match('#^https?://[^/]+(?::\d+)?(/.*)$#i', $url, $m)) {
                $pathAndQuery = $m[1];
                if (!str_starts_with($pathAndQuery, $basePath . '/') && $pathAndQuery !== $basePath && !str_starts_with($pathAndQuery, $basePath . '?')) {
                    return $appUrl . $pathAndQuery;
                }
                $subPath = substr($pathAndQuery, strlen($basePath));
                return $appUrl . ($subPath !== '' ? $subPath : '');
            } elseif (str_starts_with($url, '/') && !str_starts_with($url, $basePath . '/') && $url !== $basePath) {
                return $appUrl . $url;
            }

            return $url;
        };

        if ($request->hasSession()) {
            $session = $request->session();
            if ($session->has('url.intended')) {
                $session->put('url.intended', $normalizeUrl($session->get('url.intended')));
            }
            if ($session->has('_previous.url')) {
                $session->put('_previous.url', $normalizeUrl($session->get('_previous.url')));
            }
        }

        $response = $next($request);

        if ($request->hasSession()) {
            $session = $request->session();
            $changed = false;

            if ($session->has('url.intended')) {
                $orig = $session->get('url.intended');
                $norm = $normalizeUrl($orig);
                if ($norm !== $orig) {
                    $session->put('url.intended', $norm);
                    $changed = true;
                }
            }

            if ($session->has('_previous.url')) {
                $orig = $session->get('_previous.url');
                $norm = $normalizeUrl($orig);
                if ($norm !== $orig) {
                    $session->put('_previous.url', $norm);
                    $changed = true;
                }
            }

            if ($changed) {
                $session->save();
            }
        }

        if ($response->headers->has('Location')) {
            $response->headers->set('Location', $normalizeUrl($response->headers->get('Location')));
        }

        return $response;
    }
}
