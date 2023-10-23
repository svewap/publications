<?php

declare(strict_types=1);

namespace In2code\Publications\Controller;

use In2code\Publications\Domain\Model\Dto\Filter;
use In2code\Publications\Domain\Repository\PublicationRepository;
use In2code\Publications\Domain\Service\PublicationService;
use In2code\Publications\Pagination\NumberedPagination;
use In2code\Publications\Utility\SessionUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class PublicationController
 */
class PublicationController extends ActionController
{
    protected ?PublicationRepository $publicationRepository = null;
    protected ?PublicationService $publicationService = null;

    public function __construct(PublicationService $publicationService, PublicationRepository $publicationRepository)
    {
        $this->publicationService = $publicationService;
        $this->publicationRepository = $publicationRepository;
    }


    /**
     * @return void
     * @throws InvalidQueryException
     * @throws NoSuchArgumentException
     */
    public function listAction(): ResponseInterface
    {

        $filter = $this->createFilterObject();
        $publications = $this->publicationRepository->findByFilter($filter);

        $itemsPerPage = $filter->getRecordsPerPage();
        $maximumLinks = 8;

        $currentPage = $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1;
        $paginator = GeneralUtility::makeInstance(QueryResultPaginator::class, $publications, $currentPage, $itemsPerPage, (int)($this->settings['limit'] ?? 0), (int)($this->settings['offset'] ?? 0));
        $pagination = new SlidingWindowPagination(
            $paginator,
            $maximumLinks
        );

        if (array_key_exists('showGroupLinks', $this->settings) && (bool)$this->settings['showGroupLinks'] === true) {
            $this->view->assign(
                'groupLinks',
                $this->publicationService->getGroupedPublicationLinks(
                    $publications,
                    (int)$this->settings['groupby'],
                    $this->getContentObject()->data['uid'],
                    $itemsPerPage
                )
            );
        }

        $this->view->assign('pagination', [
            'currentPage' => $currentPage,
            'paginator' => $paginator,
            'pagination' => $pagination,
        ]);
        $this->view->assignMultiple([
            'filter' => $filter,
            'publications' => $publications,
            'data' => $this->getContentObject()->data,
            'maxItems' => count($publications),
        ]);
        return $this->htmlResponse();
    }

    /**
     * @return void
     */
    public function resetListAction(): ResponseInterface
    {
        SessionUtility::saveValueToSession('filter_' . $this->getContentObject()->data['uid'], []);
        return $this->redirect('list');
    }


    public function initializeDownloadBibtexAction()
    {
        $this->request = $this->request->withFormat('xml');
    }

    /**
     * @return ResponseInterface
     * @throws InvalidQueryException
     */
    public function downloadBibtexAction(): ResponseInterface
    {
        $this->request = $this->request->withFormat('xml');
        $filter = $this->createFilterObject();
        $publications = $this->publicationRepository->findByFilter($filter);
        $this->view->assignMultiple([
            'filter' => $filter,
            'publications' => $publications
        ]);

        return $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/x-bibtex')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Content-Disposition', 'attachment; filename="download.bib"')
            ->withBody($this->streamFactory->createStream($this->view->render()));
    }


    public function initializeDownloadXmlAction()
    {
        $this->request = $this->request->withFormat('xml');
    }


    /**
     * @return ResponseInterface
     * @throws InvalidQueryException
     */
    public function downloadXmlAction(): ResponseInterface
    {
        $filter = $this->createFilterObject();
        $publications = $this->publicationRepository->findByFilter($filter);
        $this->view->assignMultiple([
            'filter' => $filter,
            'publications' => $publications
        ]);

        return $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'text/xml')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Content-Disposition', 'attachment; filename="download.xml"')
            ->withBody($this->streamFactory->createStream($this->view->render()));
    }

    /**
     * @return Filter
     */
    protected function createFilterObject(): Filter
    {
        if ($this->request->hasArgument('filter') === false) {
            $filterArguments = SessionUtility::getSessionValue('filter_' . $this->getContentObject()->data['uid']);
        } else {
            /** @var array $filterArguments */
            $filterArguments = $this->request->getArgument('filter');
            SessionUtility::saveValueToSession('filter_' . $this->getContentObject()->data['uid'], $filterArguments);
        }
        $filter = GeneralUtility::makeInstance(Filter::class, $this->settings);
        if (!empty($filterArguments['searchterm'])) {
            $filter->setSearchterm($filterArguments['searchterm']);
        }
        if (!empty($filterArguments['year'])) {
            $filter->setYear((int)$filterArguments['year']);
        }
        if (!empty($filterArguments['authorstring'])) {
            $filter->setAuthorstring($filterArguments['authorstring']);
        }
        if (!empty($filterArguments['documenttype'])) {
            $filter->setDocumenttype($filterArguments['documenttype']);
        }
        return $filter;
    }

    /**
     * @return ContentObjectRenderer
     */
    protected function getContentObject(): ContentObjectRenderer
    {
        return $this->configurationManager->getContentObject();
    }


    /**
     * @param $paginationClass
     * @param int $maximumNumberOfLinks
     * @param $paginator
     * @return PaginationInterface #o#Э#A#M#C\In2code\Publications\Controller\PublicationController.getPagination.0|(\#o#Э#A#M#C\In2code\Publications\Controller\PublicationController.getPagination.0&\Psr\Log\LoggerAwareInterface)|(\#o#Э#A#M#C\In2code\Publications\Controller\PublicationController.getPagination.0&\TYPO3\CMS\Core\SingletonInterface)|NumberedPagination|(NumberedPagination&\Psr\Log\LoggerAwareInterface)|(NumberedPagination&\TYPO3\CMS\Core\SingletonInterface)|mixed|object|\Psr\Log\LoggerAwareInterface|string|SlidingWindowPagination|(SlidingWindowPagination&\Psr\Log\LoggerAwareInterface)|(SlidingWindowPagination&\TYPO3\CMS\Core\SingletonInterface)|\TYPO3\CMS\Core\SingletonInterface|null
     */
    protected function getPagination($paginationClass, int $maximumNumberOfLinks, $paginator): PaginationInterface
    {
        if (class_exists(\GeorgRinger\NumberedPagination\NumberedPagination::class) && $paginationClass === NumberedPagination::class && $maximumNumberOfLinks) {
            $pagination = GeneralUtility::makeInstance(NumberedPagination::class, $paginator, $maximumNumberOfLinks);
        } elseif (class_exists(SlidingWindowPagination::class) && $paginationClass === SlidingWindowPagination::class && $maximumNumberOfLinks) {
            $pagination = GeneralUtility::makeInstance(SlidingWindowPagination::class, $paginator, $maximumNumberOfLinks);
        } elseif (class_exists($paginationClass)) {
            $pagination = GeneralUtility::makeInstance($paginationClass, $paginator);
        } else {
            $pagination = GeneralUtility::makeInstance(SimplePagination::class, $paginator);
        }
        return $pagination;
    }

}
