<?php

namespace Sludio\HelperBundle\Pagination\Twig\Behaviour;

use InvalidArgumentException;

final class FixedLength extends AbstractBehaviour
{
    /**
     * @var int
     */
    private $maximumVisible;

    /**
     * @param int $maximumVisible
     *                            Maximum number of visible pages. Should never be lower than 3.
     *                            1 on each edge, 1 omitted chunk on each side, and 1 in the middle.
     *                            For example: [1][...][11][12][13][...][20]
     */
    public function __construct($maximumVisible)
    {
        $this->setMaximumVisible($maximumVisible);
    }

    /**
     * @param int $maximumVisible
     *
     * @return static
     */
    public function withMaximumVisible($maximumVisible)
    {
        $clone = clone $this;
        $clone->setMaximumVisible($maximumVisible);

        return $clone;
    }

    /**
     * @param int $maximumVisible
     *
     * @throws \InvalidArgumentException
     */
    private function setMaximumVisible($maximumVisible)
    {
        $maximumVisible = (int)$maximumVisible;
        $this->guardMaximumVisibleMinimumValue($maximumVisible);
        $this->maximumVisible = $maximumVisible;
    }

    /**
     * @return int
     */
    public function getMaximumVisible()
    {
        return $this->maximumVisible;
    }

    /**
     * @param $maximumVisible
     *
     * @throws InvalidArgumentException
     *                                   If the maximum number of visible pages is lower than 3
     */
    private function guardMaximumVisibleMinimumValue($maximumVisible)
    {
        // Maximum number of allowed visible pages should never be lower than 3.
        // 1 on each edge, 1 omitted chunk on each side, and 3 in the middle.
        // For example: [1][...][11][12][13][...][20]
        if ($maximumVisible < 3) {
            throw new InvalidArgumentException(sprintf('Maximum of number of visible pages (%d) should be at least 3.', $maximumVisible));
        }
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function getPaginationData($totalPages, $currentPage, $omittedPagesIndicator = -1)
    {
        $this->guardPaginationData($totalPages, $currentPage, $omittedPagesIndicator);

        // If the total number of pages is less than the maximum number of
        // allowed visible pages, we don't need to omit anything.
        if ($totalPages <= $this->maximumVisible) {
            return $this->getPaginationDataWithNoOmittedChunks($totalPages);
        }

        // Check if we can omit a single chunk of pages, depending on the
        // position of the current page relative to the first and last page.
        if ($this->hasSingleOmittedChunk($totalPages, $currentPage)) {
            return $this->getPaginationDataWithSingleOmittedChunk($totalPages, $currentPage, $omittedPagesIndicator);
        }

        // Otherwise omit two chunks of pages, one on each side of the current
        // page.
        return $this->getPaginationDataWithTwoOmittedChunks($totalPages, $currentPage, $omittedPagesIndicator);
    }

    /**
     * @param int $totalPages
     *
     * @return array
     */
    private function getPaginationDataWithNoOmittedChunks($totalPages)
    {
        return range(1, $totalPages);
    }

    /**
     * @return int
     */
    private function getSingleOmissionBreakpoint()
    {
        return (int)floor($this->maximumVisible / 2) + 1;
    }

    /**
     * @param int $totalPages
     * @param int $currentPage
     *
     * @return bool
     */
    public function hasSingleOmittedChunk($totalPages, $currentPage)
    {
        return $this->hasSingleOmittedChunkNearLastPage($currentPage) || $this->hasSingleOmittedChunkNearStartPage($totalPages, $currentPage);
    }

    /**
     * @param int $currentPage
     *
     * @return bool
     */
    private function hasSingleOmittedChunkNearLastPage($currentPage)
    {
        return $currentPage <= $this->getSingleOmissionBreakpoint();
    }

    /**
     * @param int $totalPages
     * @param int $currentPage
     *
     * @return bool
     */
    private function hasSingleOmittedChunkNearStartPage($totalPages, $currentPage)
    {
        return $currentPage >= $totalPages - $this->getSingleOmissionBreakpoint() + 1;
    }

    /**
     * @param int        $totalPages
     * @param int        $currentPage
     * @param int|string $omittedPagesIndicator
     *
     * @return array
     */
    private function getPaginationDataWithSingleOmittedChunk($totalPages, $currentPage, $omittedPagesIndicator)
    {
        // Determine where the omitted chunk of pages will be.
        if ($this->hasSingleOmittedChunkNearLastPage($currentPage)) {
            $rest = $this->maximumVisible - $currentPage;
            $omitPagesFrom = ((int)ceil($rest / 2)) + $currentPage;
            $omitPagesTo = $totalPages - ($this->maximumVisible - $omitPagesFrom);
        } else {
            $rest = $this->maximumVisible - ($totalPages - $currentPage);
            $omitPagesFrom = (int)ceil($rest / 2);
            $omitPagesTo = ($currentPage - ($rest - $omitPagesFrom));
        }

        // Fill each side of the pagination data, around the omitted chunk of
        // pages.
        $pagesLeft = range(1, $omitPagesFrom - 1);
        $pagesRight = range($omitPagesTo + 1, $totalPages);

        // Merge left side, omitted pages indicator, and right side together.
        return array_merge($pagesLeft, [$omittedPagesIndicator], $pagesRight);
    }

    /**
     * @param int        $totalPages
     * @param int        $currentPage
     * @param int|string $omittedPagesIndicator
     *
     * @return array
     */
    private function getPaginationDataWithTwoOmittedChunks($totalPages, $currentPage, $omittedPagesIndicator)
    {
        $visibleExceptForCurrent = $this->maximumVisible - 1;

        if ($currentPage <= ceil($totalPages / 2)) {
            $visibleLeft = ceil($visibleExceptForCurrent / 2);
            $visibleRight = floor($visibleExceptForCurrent / 2);
        } else {
            $visibleLeft = floor($visibleExceptForCurrent / 2);
            $visibleRight = ceil($visibleExceptForCurrent / 2);
        }

        // Put the left chunk of omitted pages in the middle of the visible
        // pages to the left of the current page.
        $omitPagesLeftFrom = floor($visibleLeft / 2) + 1;
        $omitPagesLeftTo = $currentPage - ($visibleLeft - $omitPagesLeftFrom) - 1;

        // Put the right chunk of omitted pages in the middle of the visible
        // pages to the right of the current page.
        $omitPagesRightFrom = ceil($visibleRight / 2) + $currentPage;
        $omitPagesRightTo = $totalPages - ($visibleRight - ($omitPagesRightFrom - $currentPage));

        // Fill the left side of pages up to the first omitted chunk, the pages
        // in the middle up to the second omitted chunk, and the right side of
        // pages.
        $pagesLeft = range(1, $omitPagesLeftFrom - 1);
        $pagesCenter = range($omitPagesLeftTo + 1, $omitPagesRightFrom - 1);
        $pagesRight = range($omitPagesRightTo + 1, $totalPages);

        // Merge everything together with omitted chunks of pages in between
        // them.
        return array_merge($pagesLeft, [$omittedPagesIndicator], $pagesCenter, [$omittedPagesIndicator], $pagesRight);
    }
}
