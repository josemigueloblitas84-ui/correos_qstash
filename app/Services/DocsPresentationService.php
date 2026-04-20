<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;

class DocsPresentationService
{
    public function preparePage(?string $content): array
    {
        if (blank($content)) {
            return [
                'content' => '',
                'toc' => '',
            ];
        }

        $dom = new DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="doc-root">' . $content . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $headings = $xpath->query('//*[@id="doc-root"]//h2 | //*[@id="doc-root"]//h3');
        $root = $xpath->query('//*[@id="doc-root"]')->item(0);

        $sections = [];
        $currentSection = null;
        $usedIds = [];

        if ($headings !== false) {
            foreach ($headings as $heading) {
                if (! $heading instanceof DOMElement) {
                    continue;
                }

                $level = strtolower($heading->tagName);
                $title = trim($heading->textContent);

                if ($title === '' || ! in_array($level, ['h2', 'h3'], true)) {
                    continue;
                }

                $baseId = Str::slug($title);
                $baseId = $baseId !== '' ? $baseId : 'seccion';
                $id = $this->makeUniqueId($baseId, $usedIds);

                $heading->setAttribute('id', $id);

                if ($level === 'h2') {
                    $sections[] = [
                        'title' => $title,
                        'href' => '#' . $id,
                        'children' => [],
                    ];

                    $currentSection = array_key_last($sections);
                    continue;
                }

                if ($currentSection !== null) {
                    $sections[$currentSection]['children'][] = [
                        'title' => $title,
                        'href' => '#' . $id,
                    ];
                }
            }
        }

        $toc = '';
        if ($sections !== []) {
            $toc = view('vendor.larecipe.partials.page-toc', [
                'sections' => $sections,
            ])->render();
        }

        return [
            'content' => $root instanceof DOMElement ? $this->innerHtml($root) : $content,
            'toc' => $toc,
        ];
    }

    private function makeUniqueId(string $baseId, array &$usedIds): string
    {
        $id = $baseId;
        $suffix = 2;

        while (in_array($id, $usedIds, true)) {
            $id = $baseId . '-' . $suffix;
            $suffix++;
        }

        $usedIds[] = $id;

        return $id;
    }

    private function innerHtml(DOMElement $root): string
    {
        $html = '';

        foreach ($root->childNodes as $child) {
            $html .= $root->ownerDocument?->saveHTML($child) ?? '';
        }

        return $html;
    }

    public function buildPageNavigation(?string $content): string
    {
        return $this->preparePage($content)['toc'];
    }

    private function resolveHeadingForAnchor(DOMElement $anchor): ?DOMElement
    {
        $current = $anchor->parentNode;

        if ($current instanceof DOMElement && strtolower($current->tagName) !== 'p') {
            $current = $anchor;
        }

        while ($current !== null) {
            $current = $current->nextSibling;

            if (! $current instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($current->tagName);

            if (in_array($tag, ['h2', 'h3'], true)) {
                return $current;
            }

            if ($tag === 'p' && trim($current->textContent) === '') {
                continue;
            }

            if (! in_array($tag, ['p'], true)) {
                break;
            }
        }

        return null;
    }
}
