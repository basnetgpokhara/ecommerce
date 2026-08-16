<?php
namespace App\Controllers;

use App\Models\Category;
use App\Models\Product;

class CategoryController extends \App\Core\Controller
{
    /** Overview of all top-level categories. */
    public function index(): void
    {
        $categories = Category::roots();
        $this->view('categories/index', compact('categories'));
    }

    /** Products within a category (and its descendants), filterable & sortable. */
    public function show(string $slug): void
    {
        $category = Category::findBySlug($slug);
        if (!$category) {
            $this->abort(404);
        }

        $filters = array_merge($this->request->query, [
            'category' => (int) $category['id'],
            'q'        => $this->request->query('q', ''),
            'min'      => $this->request->query('min'),
            'max'      => $this->request->query('max'),
            'brand'    => $this->request->query('brand'),
            'sort'     => $this->request->query('sort', 'default'),
            'page'     => $this->request->query('page', 1),
        ]);
        $listing = Product::listing($filters);
        $brands  = Product::brands();

        $this->view('categories/show', compact('category', 'listing', 'brands', 'filters'));
    }
}
