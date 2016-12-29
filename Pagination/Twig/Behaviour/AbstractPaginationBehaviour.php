<?php

namespace Sludio\HelperBundle\Pagination\Twig\Behaviour;

abstract class AbstractPaginationBehaviour implements PaginationBehaviourInterface
{
    /**
     * @param int $totalPages
     * @param int $currentPage
     * @param int|string $omittedPagesIndicator
     *
     * @throws \InvalidArgumentException
     *   When pagination data is invalid.
     */
    protected function guardPaginationData($totalPages, $currentPage, $omittedPagesIndicator = -1)
    {
        $this->guardTotalPagesMinimumValue($totalPages);
        $this->guardCurrentPageMinimumValue($currentPage);
        $this->guardCurrentPageExistsInTotalPages($totalPages, $currentPage);
        $this->guardOmittedPagesIndicatorType($omittedPagesIndicator);
        $this->guardOmittedPagesIndicatorIntValue($totalPages, $omittedPagesIndicator);
    }

    /**
     * @param int $totalPages
     *
     * @throws \InvalidArgumentException
     *   If total number of pages is lower than 1.
     */
    private function guardTotalPagesMinimumValue($totalPages)
    {
        if ($totalPages < 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Total number of pages (%d) should not be lower than 1.',
                    $totalPages
                )
            );
        }
    }

    /**
     * @param int $currentPage
     *
     * @throws \InvalidArgumentException
     *   If current page is lower than 1.
     */
    private function guardCurrentPageMinimumValue($currentPage)
    {
        if ($currentPage < 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Current page (%d) should not be lower than 1.',
                    $currentPage
                )
            );
        }
    }

    /**
     * @param int $totalPages
     * @param int $currentPage
     *
     * @throws \InvalidArgumentException
     *   If current page is higher than total number of pages.
     */
    private function guardCurrentPageExistsInTotalPages($totalPages, $currentPage)
    {
        if ($currentPage > $totalPages) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Current page (%d) should not be higher than total number of pages (%d).',
                    $currentPage,
                    $totalPages
                )
            );
        }
    }

    /**
     * @param int|string $indicator
     *
     * @throws \InvalidArgumentException
     *   If omitted pages indicator is not an int or a string.
     */
    private function guardOmittedPagesIndicatorType($indicator)
    {
        if (!is_int($indicator) && !is_string($indicator)) {
            throw new \InvalidArgumentException(
                'Omitted pages indicator should either be a string or an int.'
            );
        }
    }

    /**
     * @param int $totalPages
     * @param int|string $indicator
     *
     * @throws \InvalidArgumentException
     *   If omitted pages indicator is an int in the range of 1 and the total
     *   number of pages.
     */
    private function guardOmittedPagesIndicatorIntValue($totalPages, $indicator)
    {
        if (is_int($indicator) && $indicator >= 1 && $indicator <= $totalPages) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Omitted pages indicator (%d) should not be between 1 and total number of pages (%d).',
                    $indicator,
                    $totalPages
                )
            );
        }
    }
}
