<?php

namespace Framework\Twig;

use InvalidArgumentException;
use Twig\TwigFunction;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;

class WebpackExtension extends AbstractExtension
{

    /**
     * Path du fichier entrypoints.json
     *
     * @var string
     */
    private $entryPoints;

    public function __construct(ContainerInterface $container)
    {
        if ($container->has('twig.entrypoints')) {
            $this->entryPoints =  $container->get('twig.entrypoints') . '/entrypoints.json';
        }
        else {
            throw new InvalidArgumentException("Cette extension twig ne peut être utiliser!");
        }
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('add_script_tag_entrypoints', [$this, 'scriptTag'], ['is_safe' => ['html']]),
            new TwigFunction('add_link_tag_entrypoints', [$this, 'linkTag'], ['is_safe' => ['html']])
        ];
    }

    public function scriptTag(string $entryName): string
    {
        $entryPoints = $this->readJsonFile($this->entryPoints, $entryName);
        $tags = '';
        if (!empty($entryPoints) && array_key_exists('js', $entryPoints)) {
            foreach($entryPoints['js'] as $tag) {
            $tags .= <<<HTML
        <script src="{$tag}" defer></script>\n
HTML;
            }
        }
        return $tags;
    }

    public function linkTag(string $entryName)
    {
        $entryPoints = $this->readJsonFile($this->entryPoints, $entryName);
        $tags = '';
        if (!empty($entryPoints) && array_key_exists('css', $entryPoints)) {
            foreach($entryPoints['css'] as $tag) {
                $tags .= <<<HTML
        <link href="{$tag}" rel="stylesheet">\n
HTML;
            }
        }
        return $tags;

    }

    protected function readJsonFile(string $file, string $entryName): array
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException("Cette extension twig ne peut être utiliser, fichier invalide!");
        }

        $entry = json_decode(file_get_contents($file), true);

        $scriptTag = [];
        if (is_array($entry) && array_key_exists('entrypoints', $entry) && is_array($entry['entrypoints'])) {
            if (array_key_exists($entryName, $entry['entrypoints'])) {
                $scriptTag = $entry['entrypoints'][$entryName];
            }
        }

        return $scriptTag;
    }
}
