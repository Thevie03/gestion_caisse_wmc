<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConvertViewsToExtends extends Command
{
    protected $signature = 'app:convert-views';
    protected $description = 'Convert all views from <x-app-layout> to @extends(layouts.app)';

    public function handle()
    {
        $this->info('Conversion des vues de <x-app-layout> vers @extends...');

        $viewsPath = resource_path('views');
        $files = $this->getBladeFiles($viewsPath);

        $converted = 0;
        foreach ($files as $file) {
            if ($this->convertFile($file)) {
                $converted++;
            }
        }

        $this->info("Conversion terminée ! {$converted} fichiers convertis.");
        return Command::SUCCESS;
    }

    private function getBladeFiles($directory)
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function convertFile($filePath)
    {
        $content = File::get($filePath);

        // Vérifier si le fichier utilise <x-app-layout>
        if (strpos($content, '<x-app-layout>') === false) {
            return false;
        }

        $this->info("Conversion de: " . str_replace(resource_path('views'), '', $filePath));

        // Remplacer <x-app-layout> par @extends('layouts.app')
        $content = str_replace('<x-app-layout>', "@extends('layouts.app')\n\n@section('content')", $content);

        // Remplacer </x-app-layout> par @endsection
        $content = str_replace('</x-app-layout>', '@endsection', $content);

        // Gérer les slots x-slot name="header"
        $content = preg_replace('/<x-slot name="header">(.*?)<\/x-slot>/s', '', $content);

        // Nettoyer les espaces en trop
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

        File::put($filePath, $content);
        return true;
    }
}
