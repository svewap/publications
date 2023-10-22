<?php

declare(strict_types=1);

namespace In2code\Publications\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class AbstractRepository
 */
abstract class AbstractRepository extends Repository
{

    public ?QuerySettingsInterface $querySettings = null;

    public function injectQuerySettings(QuerySettingsInterface $querySettings): void
    {
        $this->querySettings = $querySettings;
    }

    /**
     * @return void
     */
    public function initializeObject()
    {
        $this->setDefaultQuerySettings($this->querySettings->setRespectStoragePage(false));
    }
}
