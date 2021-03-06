<?php

namespace App\Controllers;

use App\Database\Entities\Log;
use App\Database\Entities\LogEntry;
use App\Database\Repositories\LogEntryRepository;
use App\Database\Repositories\LogRepository;
use App\Exceptions\NoSuchEntityException;
use App\Helpers\ContextHelper;
use App\Helpers\FilterHelper;
use App\Helpers\MessageHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\ResourceHelper;
use App\Helpers\TimeHelper;
use League\CommonMark\CommonMarkConverter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;
use Respect\Validation\Validator as v;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Environment;

class ChangeLogController extends AbstractController
{
    /**
     * @var MessageHelper
     */
    private MessageHelper $messageHelper;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * @var RedirectHelper
     */
    private RedirectHelper $redirectHelper;

    /**
     * @var CommonMarkConverter
     */
    private CommonMarkConverter $converter;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * @var ResourceHelper
     */
    private ResourceHelper $resourceHelper;

    /**
     * @var LogEntryRepository
     */
    private LogEntryRepository $logEntryRepository;

    /**
     * @var FilterHelper
     */
    private FilterHelper $filterHelper;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(
        Environment $twig,
        ContextHelper $contextHelper,
        ResponseFactory $responseFactory,
        MessageHelper $messageHelper,
        LogRepository $logRepository,
        RedirectHelper $redirectHelper,
        CommonMarkConverter $converter,
        ResourceHelper $resourceHelper,
        LogEntryRepository $logEntryRepository,
        FilterHelper $filterHelper,
        ContainerInterface $container
    ) {
        parent::__construct($twig, $contextHelper, $responseFactory);
        $this->messageHelper = $messageHelper;
        $this->logRepository = $logRepository;
        $this->redirectHelper = $redirectHelper;
        $this->converter = $converter;
        $this->responseFactory = $responseFactory;
        $this->resourceHelper = $resourceHelper;
        $this->logEntryRepository = $logEntryRepository;
        $this->filterHelper = $filterHelper;
        $this->container = $container;
    }

    /**
     * @param string $id
     * @return Response
     */
    protected function getTableContents(Request $request, string $id)
    {
        try {
            $this->template = 'includes/changelog-table.twig';
            $entity = $this->logRepository->getById($id);
            $this->resourceHelper->setSelected($entity);
            $context = array_merge([
                'page' => [
                    'title' => 'ChangeLogs | ' . $entity->getName(),
                ],
                'navbar' => [
                    'title' => $entity->getName(),
                ],
            ], $this->filterHelper->processEntryFilters($entity, $request));
            return $this->renderResponse($context);
        } catch (NoSuchEntityException $noSuchEntityException) {
            return $this->responseFactory->createResponse(404, $noSuchEntityException->getMessage());
        } catch (\Throwable $exception) {
            return $this->responseFactory->createResponse(400, $exception->getMessage());
        }
    }

    /**
     * @param string|null $id
     * @return Response
     */
    public function get(Request $request, string $id = null)
    {
        try {
            if ($this->isAjaxRequest($request)) {
                if (!v::stringType()->uuid(4)->validate($id)) {
                    return $this->responseFactory
                        ->createResponse(400, 'Invalid Log id.');
                }
                return $this->getTableContents($request, $id);
            }
            $this->template = 'pages/changelog.twig';
            $this->resourceHelper->setLoadLogs(true);
            $context = [
                'page' => [
                    'title' => 'ChangeLogs'
                ],
                'navbar' => [],
            ];
            if (!empty($id) && Uuid::isValid($id)) {
                $entity = $this->logRepository->getById($id);
                $this->resourceHelper->setSelected($entity);
                $context['navbar']['title'] = $entity->getName();
                $context['page']['title'] .= ' | ' . $entity->getName();
                $context = array_merge($context, $this->filterHelper->processEntryFilters($entity, $request));
            }
            return $this->renderResponse($context);
        } catch (NoSuchEntityException $exception) {
            $this->messageHelper->addMessage('error', $exception->getMessage());
            return $this->redirectHelper->tmp('/changelogs');
        }
    }

    /**
     * @param string $id
     * @return Response
     */
    public function edit(string $id)
    {
        try {
            if (empty($id) || !Uuid::isValid($id)) {
                $this->messageHelper->addMessage('error', 'Invalid Id.');
                return $this->redirectHelper->tmp('/changelogs');
            }
            $this->template = 'pages/changelog.twig';
            $entity = $this->logRepository->getById($id);
            $this->resourceHelper->setSelected($entity);
            $this->resourceHelper->setLoadLogs(true);
            $this->resourceHelper->setEditMode(true);
            $context = [
                'page' => [
                    'title' => 'ChangeLogs | Edit | ' . $entity->getName(),
                ],
                'navbar' => [
                    'title' => $entity->getName(),
                ],
            ];
            return $this->renderResponse($context);
        } catch (NoSuchEntityException $exception) {
            $this->messageHelper->addMessage('error', $exception->getMessage());
            return $this->redirectHelper->tmp('/changelogs');
        }
    }

    /**
     * @return Response
     */
    public function create()
    {
        $this->resourceHelper->setLoadLogs(true);
        $context = [
            'navbar' => [],
            'entity' => [
                'log' => [
                    'id' => Uuid::uuid4()->toString(),
                    'name' => '',
                    'description' => '',
                ]
            ],
            'msg' => [],
        ];
        $this->template = 'pages/changelog-create.twig';;
        return $this->renderResponse($context);
    }

    /**
     * @param string $id
     */
    public function delete(string $id)
    {
        try {
            $entity = $this->logRepository->getById($id);
            $this->logRepository->delete($entity);
            $this->messageHelper->addMessage(
                'success',
                'Successfully deleted Log Entity with id: "' . $id . '".'
            );
        } catch (\Exception $exception) {
            $this->messageHelper->addMessage('error', $exception->getMessage());
        }
        return $this->redirectHelper->tmp('/changelogs');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function save(Request $request, Response $response)
    {
        $existed = false;
        $isAjax = $this->isAjaxRequest($request);
        try {
            $v = v::arrayType()->notEmpty()->keySet(
                v::key('id', v::stringVal()->notEmpty()->uuid(4)),
                v::key('name', v::stringVal()->notEmpty()),
                v::key('description', v::stringVal())
            );
            $v->check(($body = $request->getParsedBody()));
            $id = $body['id'];
            $name = $body['name'];
            $description = $body['description'];

            try {
                $entity = $this->logRepository->getById($id);
                $existed = $entity->getId();
            } catch (NoSuchEntityException $exception) {
                $entity = new Log();
            }
            $entity->setName($name);
            $entity->setDescription($description);
            $this->logRepository->save($entity);
            $text = ($existed === false ? 'Created new' : 'Updated');
            $this->messageHelper->addMessage('success', 'Successfully ' . $text . ' ChangeLog');
            if ($isAjax) {
                $response->getBody()->write('');
                $response->withStatus(204);
                return $response;
            }
            return $this->redirectHelper->tmp('/changelogs/' . $entity->getId());
        } catch (\Throwable $exception) {
            $link = '/changelogs/create';
            if ($existed) {
                $link = '/changelogs/' . $existed;
            }
            if ($isAjax) {
                $resp = $this->responseFactory->createResponse(400, $exception->getMessage());
                $resp->getBody()->write($exception->getMessage());
                return $resp;
            }
            $this->messageHelper->addMessage('error', $exception->getMessage());
            return $this->redirectHelper->tmp($link);
        }
    }

    public function showLogEntry(Request $request, string $id)
    {
        try {
            $this->template = 'pages/show-log-entry.twig';
            $entry = $this->logEntryRepository->getById($id);
            $this->resourceHelper->setLoadLogs(true);
            $this->resourceHelper->setSelected($entry->getLog());
            return $this->renderResponse([
                'page' => [
                    'title' => 'Changelogs | Show Entry'
                ],
                'navbar' => [
                    'title' => 'Show Entry'
                ],
                'entry' => $entry,
                'selected' => [
                    'edit_link' => '/changelogs/entry/' . $id . '/edit',
                ]
            ]);
        } catch (\Throwable $throwable) {
            $this->messageHelper->addMessage('error', $throwable->getMessage());
            $ref = $this->getReferer($request);
            if (!empty($ref)) {
                return $this->redirectHelper->tmp($ref);
            }
            return $this->redirectHelper->tmp('/changelogs');
        }
    }

    public function newLogEntry(string $id)
    {
        try {
            $this->template = 'pages/new-log-entry.twig';
            $this->resourceHelper->setLoadLogs(true);
            $entity = $this->logRepository->getById($id);
            $this->resourceHelper->setSelected($entity);
            $this->resourceHelper->setEntryMode('add');
            return $this->renderResponse([
                'page' => [
                    'title' => 'Changelogs | Add Entry'
                ],
                'navbar' => [
                    'title' => 'Add Entry'
                ],
                'identifier' => Uuid::uuid4()->toString(),
            ]);
        } catch (\Throwable $throwable) {
            $this->messageHelper->addMessage('error', $throwable->getMessage());
            return $this->redirectHelper->tmp('/changelogs');
        }
    }

    public function deleteLogEntry(Request $request, string $id)
    {
        $msg = 'You successfully deleted a LogEntry.';
        $isAjax = $this->isAjaxRequest($request);
        $ref = $this->getReferer($request);
        try {
            $entry = $this->logEntryRepository->getById($id);
            $this->logEntryRepository->delete($entry);
            if (!$isAjax) {
                $this->messageHelper->addMessage('success', $msg);
                if (!empty($ref)) {
                    return $this->redirectHelper->tmp($ref);
                }
                return $this->redirectHelper->tmp('/changelogs');
            }
            return $this->responseFactory->createResponse(204, $msg);
        } catch (\Throwable $throwable) {
            $this->messageHelper->addMessage('error', $throwable->getMessage());
            if (!empty($ref)) {
                return $this->redirectHelper->tmp($ref);
            }
            return $this->redirectHelper->tmp('/changelogs');
        }
    }

    public function editLogEntry(string $id)
    {
        try {
            $this->template = 'pages/new-log-entry.twig';
            $this->resourceHelper->setLoadLogs(true);
            $entity = $this->logEntryRepository->getById($id);
            $this->resourceHelper->setSelected($entity->getLog());
            $this->resourceHelper->setEntryMode('add');
            return $this->renderResponse([
                'page' => [
                    'title' => 'Changelogs | Edit Entry'
                ],
                'navbar' => [
                    'title' => 'Edit Entry'
                ],
                'identifier' => $entity->getId(),
                'entry' => $entity,
            ]);
        } catch (\Throwable $throwable) {
            $this->messageHelper->addMessage('error', $throwable->getMessage());
            return $this->redirectHelper->tmp('/changelogs');
        }
    }

    public function saveLogEntry(Request $request)
    {
        $data = $request->getParsedBody();
        try {
            $v = v::arrayType()->notEmpty()->allOf(
                v::key('id', v::stringVal()->notEmpty()->uuid(4)),
                v::key('log_id', v::stringVal()->notEmpty()->uuid(4)),
                v::key('initiated_by', v::stringVal()->notEmpty()->length(null, 200)),
                v::key('tech', v::stringVal()->notEmpty()->length(null, 200)->email()),
                v::key('change_description', v::stringVal()->notEmpty()),
                v::key('device', v::stringVal(), false),
                v::key('rollback_description', v::stringVal(), false),
                v::oneOf(
                    v::key('now', v::stringVal()->notEmpty()->equals('1')),
                    v::key('created_at', v::stringVal()->notEmpty()->dateTime('d-m-Y H:i:s')),
                ),
            );
            $v->check($data);
            $id = $data['id'];
            $logId = $data['log_id'];
            $log = $this->logRepository->getById($logId);

            $initiatedBy = $data['initiated_by'];
            $tech = $data['tech'];
            $changeDescription = $data['change_description'];
            $device = $data['device'] ?? null;
            $rollbackDescription = $data['rollback_description'] ?? null;

            $updated = false;
            try {
                $entity = $this->logEntryRepository->getById($id);
                $updated = true;
            } catch (NoSuchEntityException $exception) {
                $entity = new LogEntry();
            }

            $entity->setLog($log);
            $log->getEntries()->add($entity);
            $entity->setInitiatedBy($initiatedBy);
            $entity->setTech($tech);
            $entity->setChangeDescription($changeDescription);
            $entity->setDevice($device);
            $entity->setRollbackDescription($rollbackDescription);
            if (!isset($data['now'])) {
                $convertedDate = TimeHelper::convertTimezone(
                    \DateTimeImmutable::createFromFormat(
                        'd-m-Y H:i:s',
                        $data['created_at'],
                        new \DateTimeZone($this->container->get('locale')['timezone'])
                    ),
                    'UTC'
                );
                $entity->setCreatedAt($convertedDate);
            }

            $this->logEntryRepository->save($entity);
            $msg = ($updated ? 'Updated' : 'Created');
            $this->messageHelper->addMessage('success', 'Successfully ' . $msg . ' Log Entry');
            return $this->redirectHelper->tmp('/changelogs/entry/' . $entity->getId());
        } catch (\Throwable $throwable) {
            $this->messageHelper->addMessage('error', $throwable->getMessage());
            $ref = $this->getReferer($request);
            if (!empty($ref)) {
                return $this->redirectHelper->tmp($ref);
            }
            if (!empty($data['log_id']) && is_string($data['log_id'])) {
                return $this->redirectHelper->tmp('/changelogs/' . $data['log_id'] . '/entry/new');
            }
            return $this->redirectHelper->tmp('/changelogs');
        }
    }
}