<?php

namespace App\Http\Controllers;

use App\Services\TrackingLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __construct(
        protected TrackingLinkService $linkService,
    ) {}

    /**
     * Handle the redirection based on the slug.
     */
    public function __invoke(string $slug): RedirectResponse
    {
        $link = $this->linkService->resolveSlug($slug);

        if (!$link) {
            abort(404, 'Tracking link not found or invalid');
        }

        return redirect()->away($link->redirect_url);
    }
}
