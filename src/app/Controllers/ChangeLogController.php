<?php

namespace App\Controllers;

use App\Database\Entities\Log;
use App\Database\Entities\LogEntry;
use App\Database\Repositories\LogRepository;
use App\Exceptions\NoSuchEntityException;
use App\Helpers\ContextHelper;
use App\Helpers\MessageHelper;
use App\Helpers\RedirectHelper;
use League\CommonMark\CommonMarkConverter;
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

    public function __construct(
        Environment $twig,
        ContextHelper $contextHelper,
        ResponseFactory $responseFactory,
        MessageHelper $messageHelper,
        LogRepository $logRepository,
        RedirectHelper $redirectHelper,
        CommonMarkConverter $converter
    ) {
        parent::__construct($twig, $contextHelper, $responseFactory);
        $this->messageHelper = $messageHelper;
        $this->logRepository = $logRepository;
        $this->redirectHelper = $redirectHelper;
        $this->converter = $converter;
    }

    /**
     * @return array[]
     */
    protected function getLogs()
    {
        return array_map(function(Log $log) {
            return [
                'id' => $log->getId(),
                'name' => $log->getName(),
                'link' => '/changelogs/' . $log->getId()
            ];
        }, $this->logRepository->getAllLogs());
    }

    /**
     * @param string|null $id
     * @return Response
     */
    public function get(Response $response, string $id = null)
    {
        try {
            $this->template = 'pages/changelog.twig';
            $context = [
                'page' => [
                    'title' => 'ChangeLogs'
                ],
                'navbar' => [],
                'logs' => $this->getLogs(),
                'msg' => [],
            ];
            if (!empty($id) && Uuid::isValid($id)) {
                $entity = $this->logRepository->getById($id);
                $context['selected'] = [
                    'id' => $entity->getId(),
                    'name' => $entity->getName(),
                    'edit_link' => '/changelogs/' . $entity->getId() . '/edit',
                    'delete_link' => '/changelogs/' . $entity->getId() . '/delete',
                    'entries' => [], // TODO: load entries here + filters
                ];
                $context['navbar']['title'] = $entity->getName();
                $context['page']['title'] .= ' | ' . $entity->getName();
                if (!empty($entity->getDescription())) {
                    $context['selected']['markdown'] = $this->converter->convertToHtml($entity->getDescription());
                }
            }
            return $this->renderResponse($context);
        } catch (NoSuchEntityException $exception) {
            $this->messageHelper->addMessage('error', $exception->getMessage());
            return $this->redirectHelper->tmp($response, '/changelogs');
        }
    }

    /**
     * @param Response $response
     * @param string $id
     * @return Response
     */
    public function edit(Response $response, string $id)
    {
        try {
            if (empty($id) || !Uuid::isValid($id)) {
                $this->messageHelper->addMessage('error', 'Invalid Id.');
                return $this->redirectHelper->tmp($response, '/changelogs');
            }
            $this->template = 'pages/changelog.twig';
            $entity = $this->logRepository->getById($id);
            $context = [
                'page' => [
                    'title' => 'ChangeLogs | Edit | ' . $entity->getName(),
                ],
                'navbar' => [
                    'title' => $entity->getName(),
                ],
                'logs' => $this->getLogs(),
                'msg' => [],
                'selected' => [
                    'id' => $entity->getId(),
                    'name' => $entity->getName(),
                    'edit_mode' => true,
                    'entries' => [], // TODO: load entries here + filters
                ],
            ];
            if (!empty($entity->getDescription())) {
                $context['selected']['description'] = $entity->getDescription();
            }
            return $this->renderResponse($context);
        } catch (NoSuchEntityException $exception) {
            $this->messageHelper->addMessage('error', $exception->getMessage());
            return $this->redirectHelper->tmp($response, '/changelogs');
        }
    }

    /**
     * @return Response
     */
    public function create()
    {
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
            'logs' => $this->getLogs(),
        ];

        $message = null;
        if ($this->messageHelper->hasMessage('error')) {
            $context['msg']['error'] = $this->messageHelper->getMessage('error');
        }
        if ($this->messageHelper->hasMessage('success')) {
            $context['msg']['success'] = $this->messageHelper->getMessage('success');
        }
        $this->messageHelper->reset();
        $this->template = 'pages/changelog-create.twig';;
        return $this->renderResponse($context);
    }

    /**
     * @param Response $response
     * @param string $id
     */
    public function delete(Response $response, string $id)
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
        return $this->redirectHelper->tmp($response, '/changelogs');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function save(Request $request, Response $response)
    {
        try {
            $v = v::arrayType()->keySet(
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
            } catch (NoSuchEntityException $exception) {
                $entity = new Log();
            }
            $entity->setName($name);
            $entity->setDescription($description);
            $this->logRepository->save($entity);
            $this->messageHelper->addMessage('success', 'Successfully Created new ChangeLog');
            return $this->redirectHelper->tmp($response, '/changelogs/create');
        } catch (\Exception $exception) {
            $this->messageHelper->addMessage('error', $exception->getMessage());
            return $this->redirectHelper->tmp($response, '/changelogs/create');
        }
    }
}