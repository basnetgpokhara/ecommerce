<?php
namespace App\Controllers;

use App\Models\Page;

class PageController extends \App\Core\Controller
{
    /** Render a published static CMS page (About, Privacy, Terms, Returns ...). */
    public function show(string $slug): void
    {
        $page = Page::publishedBySlug($slug);
        if (!$page) {
            $this->abort(404);
        }
        $this->view('pages/show', compact('page'));
    }
}
