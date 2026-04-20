<?php

namespace App\Support\Larecipe;

use App\Services\DocsAccessService;
use BinaryTorch\LaRecipe\DocumentationRepository as BaseDocumentationRepository;
use BinaryTorch\LaRecipe\Models\Documentation;

class DocumentationRepository extends BaseDocumentationRepository
{
    public function __construct(
        Documentation $documentation,
        protected DocsAccessService $docsAccessService
    ) {
        parent::__construct($documentation);
    }

    public function get($version, $page = null, $data = [])
    {
        $repository = parent::get($version, $page, $data);

        $this->index = $this->docsAccessService->filterIndexHtml($this->index);

        return $repository;
    }

    public function search($version)
    {
        return $this->docsAccessService->filterSearchResults(
            parent::search($version)
        );
    }
}
