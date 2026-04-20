<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Contracts\Auth\Authenticatable;

class DocsAccessService
{
    public function canViewDocumentation(?Authenticatable $user, mixed $documentation): bool
    {
        $statusCode = (int) data_get($documentation, 'statusCode', 200);
        $page = data_get($documentation, 'currentSection')
            ?: data_get($documentation, 'sectionPage')
            ?: config('larecipe.docs.landing');

        if ($statusCode === 404 || blank($page)) {
            return true;
        }

        return $this->canViewPage($user, $page);
    }

    public function canViewPage(?Authenticatable $user, ?string $page): bool
    {
        $page = $this->normalizePageKey($page);

        if (blank($page)) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $rules = config("docs_access.pages.{$page}");

        if (! is_array($rules)) {
            return (bool) config('docs_access.default_visibility', false);
        }

        if (($rules['public'] ?? false) === true) {
            return true;
        }

        $roles = $rules['roles'] ?? [];
        if ($roles !== [] && method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return true;
        }

        $anyPermissions = $rules['any_permissions'] ?? [];
        if ($anyPermissions !== [] && method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission($anyPermissions)) {
            return true;
        }

        $allPermissions = $rules['all_permissions'] ?? [];
        if ($allPermissions !== [] && $this->hasAllPermissions($user, $allPermissions)) {
            return true;
        }

        return false;
    }

    public function filterIndexHtml(?string $html, ?Authenticatable $user = null): ?string
    {
        if (blank($html)) {
            return $html;
        }

        $user ??= auth()->user();

        if ($this->isSuperAdmin($user)) {
            return $html;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $anchors = iterator_to_array($xpath->query('//a[@href]') ?: []);

        foreach ($anchors as $anchor) {
            $page = $this->extractPageFromHref($anchor->getAttribute('href'));

            if ($page === null || $this->canViewPage($user, $page)) {
                continue;
            }

            $item = $this->findClosestElement($anchor, 'li');

            if ($item !== null && $item->parentNode !== null) {
                $item->parentNode->removeChild($item);
            }
        }

        $this->cleanupIndexMarkup($dom);

        $html = $dom->saveHTML();
        $html = preg_replace('/^<\?xml[^>]+>\s*/', '', $html) ?? $html;

        return trim($html);
    }

    public function filterSearchResults(array $results, ?Authenticatable $user = null): array
    {
        $user ??= auth()->user();

        if ($this->isSuperAdmin($user)) {
            return $results;
        }

        return array_values(array_filter($results, function (array $page) use ($user) {
            return $this->canViewPage($user, $page['path'] ?? null);
        }));
    }

    private function isSuperAdmin(?Authenticatable $user): bool
    {
        if (! $user || ! method_exists($user, 'hasAnyRole')) {
            return false;
        }

        $roles = config('docs_access.super_admin_roles', []);

        return $roles !== [] && $user->hasAnyRole($roles);
    }

    private function hasAllPermissions(Authenticatable $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! method_exists($user, 'can') || ! $user->can($permission)) {
                return false;
            }
        }

        return true;
    }

    private function extractPageFromHref(string $href): ?string
    {
        $path = parse_url($href, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        if (count($segments) < 3) {
            return null;
        }

        return end($segments) ?: null;
    }

    private function normalizePageKey(?string $page): ?string
    {
        if (! is_string($page) || $page === '') {
            return $page;
        }

        $path = parse_url($page, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : $page;

        return trim($path, '/');
    }

    private function findClosestElement(DOMNode $node, string $tagName): ?DOMElement
    {
        while ($node->parentNode !== null) {
            $node = $node->parentNode;

            if ($node instanceof DOMElement && $node->tagName === $tagName) {
                return $node;
            }
        }

        return null;
    }

    private function cleanupIndexMarkup(DOMDocument $dom): void
    {
        do {
            $changed = false;
            $xpath = new DOMXPath($dom);

            $emptyLists = iterator_to_array($xpath->query('//ul[not(.//li)]') ?: []);
            foreach ($emptyLists as $list) {
                if ($list->parentNode !== null) {
                    $list->parentNode->removeChild($list);
                    $changed = true;
                }
            }

            $emptyItems = iterator_to_array($xpath->query('//li[not(.//a)]') ?: []);
            foreach ($emptyItems as $item) {
                if ($item->parentNode !== null) {
                    $item->parentNode->removeChild($item);
                    $changed = true;
                }
            }
        } while ($changed);
    }
}
