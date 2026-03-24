<?php

declare(strict_types=1);

namespace APB\HorizonUI\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Laravel\Horizon\Horizon;

class SwapHorizonAssets
{
    /**
     * Handle an incoming request.
     *
     * Replaces Horizon's inlined JS bundle with our enhanced version
     * that includes job name search/filter functionality.
     *
     * Only modifies HTML responses (not API JSON), so Horizon's API
     * endpoints are unaffected by this middleware.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (! $response instanceof Response) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');

        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false || ! str_contains($content, 'window.Horizon')) {
            return $response;
        }

        $enhancedJs = $this->enhancedJs();

        // Replace the <script type="module"> block that contains window.Horizon.
        // This is the unique marker for Horizon's inlined JS bundle.
        $pattern = '/<script\s+type="module">\s*window\.Horizon\s*=.*?<\/script>/s';
        $replaced = preg_replace($pattern, $enhancedJs, $content, 1);

        if ($replaced !== null) {
            $response->setContent($replaced);
        }

        return $response;
    }

    /**
     * Get the enhanced JS script tag with our modified bundle.
     */
    protected function enhancedJs(): string
    {
        $js = @file_get_contents(__DIR__ . '/../../../dist/app.js');

        if ($js === false) {
            // Fallback — let original Horizon JS through unchanged.
            return Horizon::js()->toHtml();
        }

        $horizon = Js::from(Horizon::scriptVariables());

        return (new HtmlString(<<<HTML
            <script type="module">
                window.Horizon = {$horizon};
                {$js}
            </script>
            HTML))->toHtml();
    }
}
