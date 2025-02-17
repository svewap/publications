<?php

declare(strict_types=1);

namespace In2code\Publications\Domain\Service;

use In2code\Publications\Domain\Model\Dto\Filter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class PublicationService
{
    public function getGroupedPublicationLinks(array $publications, int $groupBy, int $ceIdentifier, int $itemsPerPage): array
    {
        $groupLinks = [];
        $linkHrefPrefix = '';

        switch ($groupBy) {
            case Filter::GROUP_BY_YEAR:
            case Filter::GROUP_BY_YEAR_AND_TYPE:
                $groupByMethod = 'getYear';
                $linkHrefPrefix = 'c';
                break;
            case Filter::GROUP_BY_TYPE:
                $groupByMethod = 'getBibtype';
                break;
        }

        if (!empty($groupByMethod)) {
            $count = 0;
            $page = 0;
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            foreach ($publications as $publication) {
                if ($count % $itemsPerPage === 0) {
                    $page++;
                }
                $url = $uriBuilder->reset()->setArguments(['tx_publications_pi1' => ['currentPage' => $page]])->build();

                $localizedBibType =
                    LocalizationUtility::translate('bibtype.' . $publication->{$groupByMethod}(), 'publications');

                if (!array_key_exists($publication->{$groupByMethod}(), $groupLinks) &&
                    !empty($publication->{$groupByMethod}())
                ) {
                    $groupLinks[$publication->{$groupByMethod}()] = [
                        'title' => $publication->{$groupByMethod}(),
                        'link' => $url . '#' . $linkHrefPrefix . $publication->{$groupByMethod}() . '-' .
                            $ceIdentifier
                    ];

                    // localize title if necessary
                    if ($groupBy === Filter::GROUP_BY_TYPE && !empty($localizedBibType)) {
                        $groupLinks[$publication->{$groupByMethod}()]['title'] = $localizedBibType;
                    }
                }

                $count++;
            }
        }

        return $groupLinks;
    }
}
