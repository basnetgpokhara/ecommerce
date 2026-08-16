<?php
namespace App\Core;

/**
 * View — renders a template inside a layout. Template paths are relative
 * to /app/views (e.g. "home/index"). Data is extracted into template scope.
 */
class View
{
    public function __construct(
        public string $template,
        public array $data = []
    ) {}

    public function render(string $layout = 'public'): void
    {
        $file = VIEW_PATH . '/' . $this->template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: {$this->template}");
        }
        $content = $this->template; // available to the layout
        extract($this->data, EXTR_SKIP);
        require VIEW_PATH . '/layouts/' . $layout . '.php';
    }

    /** Include a partial with its own isolated data. */
    public static function partial(string $name, array $data = []): void
    {
        $file = VIEW_PATH . '/partials/' . $name . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("Partial not found: {$name}");
        }
        extract($data, EXTR_SKIP);
        require $file;
    }
}
