<?php

namespace Framework\Router\Loader;

class DirectoryLoader extends FileLoader
{

    /**
     * Find all php files with @Route annotations
     *
     * @param string $dir
     * @return Route[]|null
     */
    public function load(string $dir): ?array
    {
        if (!is_dir($dir)) {
            return parent::load($dir);
        }

        $files = $this->getFiles($dir);

        $routes = [];
        foreach ($files as $file) {
            $res = parent::load($file);
            if ($res) {
                $routes[] = $res;
            }
        }
        return $routes;
    }


    /**
     * Filtre les fichiers php recursivement a partir de path
     *
     * @param string $path
     * @return array
     */
    public function getFiles(string $path): array
    {
        // from https://stackoverflow.com/a/41636321
        return iterator_to_array(
            new \CallbackFilterIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $path,
                        \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS
                    )
                ),
                function (\SplFileInfo $file) {
                    return $file->isFile() &&
                        ('.' !== substr($file->getBasename(), 0, 1) &&
                            '.php' === substr($file->getFilename(), -4));
                }
            )
        );
    }
}
