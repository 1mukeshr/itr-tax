<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        extract($data);
        $app = require __DIR__ . '/../../config/app.php';
        $authUser = Auth::user();
        $flash = Session::getFlash();

        ob_start();
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            require __DIR__ . '/../Views/' . $layout . '.php';
        } else {
            echo $content;
        }
    }

    public static function partial(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/' . $view . '.php';
    }
}
