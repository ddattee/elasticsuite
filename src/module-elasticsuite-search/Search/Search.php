<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteSearch\Search
 * @author    David Dattée <david.dattee@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticSuiteSearch\Search;

use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Search\Api\SearchInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Framework\Search\SearchResponseBuilder;
use Magento\Search\Model\SearchEngine;
use Smile\ElasticSuiteCore\Search\Request\Builder;

/**
 * Substitution Search class for Magento\Search\Search
 *
 * @category Smile
 * @package  Smile\ElasticSuiteSearch\Search
 * @author   David Dattée <david.dattee@smile.fr>
 */
class Search implements SearchInterface
{
    /**
     * @var Builder
     */
    protected $requestBuilder;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var SearchEngine
     */
    protected $searchEngine;

    /**
     * @var SearchResponseBuilder
     */
    protected $searchResponseBuilder;

    /**
     * Search constructor.
     *
     * @param Builder                $requestBuilder        Request Builder
     * @param ScopeResolverInterface $scopeResolver         Scope resolver
     * @param SearchEngineInterface  $searchEngine          Search engine
     * @param SearchResponseBuilder  $searchResponseBuilder Search Response Builder
     */
    public function __construct(
        Builder $requestBuilder,
        ScopeResolverInterface $scopeResolver,
        SearchEngineInterface $searchEngine,
        SearchResponseBuilder $searchResponseBuilder
    ) {
        $this->requestBuilder           = $requestBuilder;
        $this->scopeResolver            = $scopeResolver;
        $this->searchEngine             = $searchEngine;
        $this->searchResponseBuilder    = $searchResponseBuilder;
    }

    /**
     * Run the search
     *
     * @param SearchCriteriaInterface $searchCriteria Search criterias
     *
     * @return mixed
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $scope = $this->scopeResolver->getScope();
        $searchRequest = $this->requestBuilder->create(
            $scope->getId(),
            $searchCriteria->getRequestName(),
            $searchCriteria->getCurrentPage() * $searchCriteria->getPageSize(),
            $searchCriteria->getPageSize(),
            $this->getQueryText($searchCriteria),
            (array) $searchCriteria->getSortOrders(),
            $this->getRootFilters(),
            $this->getFacets()
        );
        $searchResponse = $this->searchEngine->search($searchRequest);

        return $this->searchResponseBuilder->build($searchResponse)->setSearchCriteria($searchCriteria);
    }

    /**
     * Get query text from searchCriterias
     *
     * @param SearchCriteriaInterface $searchCriteria Search criterias
     *
     * @return string
     */
    private function getQueryText(SearchCriteriaInterface $searchCriteria)
    {
        $queryText = '';
        foreach ($searchCriteria->getFilterGroups() as $group) {
            foreach ($group->getFilters() as $filter) {
                $queryText .= (strlen($queryText) > 0 ? ',' : '') . $filter->getValue();
            }
        }

        return $queryText;
    }

    /**
     * Get root filters
     *
     * @return array
     */
    private function getRootFilters()
    {
        return [
            'stock.is_in_stock' => Status::STATUS_IN_STOCK,
            'visibility' => [Visibility::VISIBILITY_IN_SEARCH]
        ];
    }

    /**
     * Get facets
     *
     * @return array
     */
    private function getFacets()
    {
        return [];
    }
}