<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Mautic\CoreBundle\Controller\AbstractFormController;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaign;
use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaignRepository;
use MauticPlugin\MauticTriggerdialogBundle\Model\TriggerCampaignModel;
use MauticPlugin\MauticTriggerdialogBundle\Utility\SsoUtility;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TriggerCampaignController extends AbstractFormController
{
    const PERMISSIONS = [
        'create' => 'triggerdialog:campaigns:create',
        'delete' => 'triggerdialog:campaigns:delete',
        'edit' => 'triggerdialog:campaigns:edit',
        'publish' => 'triggerdialog:campaigns:publish',
        'view' => 'triggerdialog:campaigns:view',
    ];

    const ROUTES = [
        'action' => 'mautic_triggerdialog_action',
        'index' => 'mautic_triggerdialog_index',
    ];

    const SESSION_VARS = [
        'limit' => 'plugin.triggerdialog.limit',
        'orderBy' => 'plugin.triggerdialog.orderby',
        'orderByDir' => 'plugin.triggerdialog.orderbydir',
        'page' => 'plugin.triggerdialog.page',
        'search' => 'plugin.triggerdialog.search',
    ];

    const THEMES = [
        'variables' => 'MauticTriggerdialogBundle:FormTheme\Variables',
    ];

    const TEMPLATES = [
        'form' => 'MauticTriggerdialogBundle:TriggerCampaign:form.html.php',
        'index' => 'MauticTriggerdialogBundle:TriggerCampaign:index',
        'list' => 'MauticTriggerdialogBundle:TriggerCampaign:list.html.php',
    ];

    const ACTIVE_LINK = '#mautic_triggerdialog_index';

    const MAUTIC_CONTENT = 'triggerdialog';

    protected $session;

    /**
     * @param int $page
     *
     * @return Response
     */
    public function indexAction($page = 1)
    {
        //set some permissions
        $permissions = $this->getPermissions();
        if (!$permissions[self::PERMISSIONS['view']]) {
            return $this->accessDenied();
        }

        $viewParameters = [];
        $viewParameters['permissions'] = $permissions;

        $this->setSession();
        $coreParametersHelper = $this->getCoreParametersHelper();
        $viewParameters['ssoUrl'] = $this->getSSOUrl($coreParametersHelper);
        $viewParameters['template'] = $this->getTemplate();
        $viewParameters['configInvalid'] = !$this->checkConfiguration($coreParametersHelper);

        $limit = $this->getLimit();
        $start = $this->getStart($limit, $page);
        $search = $this->getSearch();
        $orderBy = $this->getOrderBy();
        $orderByDir = $this->getOrderByDir();

        $triggerCampaigns = $this->getTriggerCampaigns($this->getFilter($search), $start, $limit, $orderBy, $orderByDir);
        $count = count($triggerCampaigns);

        if ($count && $count < ($start + 1)) {
            return $this->redirectToLastPage($count, $limit);
        }

        $this->setPage($page);

        $viewParameters['searchValue'] = $search;
        $viewParameters['items'] = $triggerCampaigns;
        $viewParameters['searchValue'] = $search;
        $viewParameters['page'] = $page;
        $viewParameters['limit'] = $limit;

        return $this->delegateView([
            'viewParameters' => $viewParameters,
            'contentTemplate' => self::TEMPLATES['list'],
            'passthroughVars' => [
                'activeLink' => self::ACTIVE_LINK,
                'mauticContent' => self::MAUTIC_CONTENT,
                'route' => $this->generateUrl(self::ROUTES['index'], ['page' => $page]),
            ],
        ]);
    }

    /**
     * @return Response
     */
    public function newAction()
    {
        $this->setSession();

        if (!$this->get('mautic.security')->isGranted(self::PERMISSIONS['create'])) {
            return $this->accessDenied();
        }

        $model = $this->getModel(TriggerCampaignModel::NAME);
        $triggerCampaign = $model->getEntity();
        $form = $model->createForm(
            $triggerCampaign,
            $this->get('form.factory'),
            $this->generateUrl(self::ROUTES['action'], ['objectAction' => 'new'])
        );

        if ($this->request->getMethod() === 'POST') {
            $template = self::TEMPLATES['index'];
            $viewParameters = ['page' => $this->session->get(self::SESSION_VARS['page'], 1)];
            $returnUrl = $this->generateUrl(self::ROUTES['index'], $viewParameters);
            $valid = false;

            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($triggerCampaign);

                    $this->addFlash('mautic.core.notice.created', [
                        '%name%' => $triggerCampaign->getName(),
                        '%menu_link%' => self::ROUTES['index'],
                        '%url%' => $this->generateUrl(self::ROUTES['index'], [
                            'objectAction' => 'edit',
                            'objectId' => $triggerCampaign->getId(),
                        ]),
                    ]);

                    if (!$form->get('buttons')->get('save')->isClicked()) {
                        //return edit view so that all the session stuff is loaded
                        return $this->editAction($triggerCampaign->getId(), true);
                    }
                }
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    [
                        'returnUrl' => $returnUrl,
                        'viewParameters' => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => [
                            'activeLink' => self::ACTIVE_LINK,
                            'mauticContent' => self::MAUTIC_CONTENT,
                        ],
                    ]
                );
            }
        }

        return $this->delegateView([
            'viewParameters' => [
                'form' => $this->setFormTheme($form, self::TEMPLATES['form'], self::THEMES['variables']),
            ],
            'contentTemplate' => self::TEMPLATES['form'],
            'passthroughVars' => [
                'activeLink' => self::ACTIVE_LINK,
                'mauticContent' => self::MAUTIC_CONTENT,
                'route' => $this->generateUrl(self::ROUTES['action'], ['objectAction' => 'new']),
            ],
        ]);
    }

    /**
     * @param int  $objectId
     * @param bool $ignorePost
     * @param bool $clone
     *
     * @return Response
     */
    public function editAction($objectId, $ignorePost = false, $clone = false)
    {
        $this->setSession();
        $postActionVars = $this->getPostActionVars();

        try {
            $triggerCampaign = $this->getTriggerCampaign($objectId);
            $objectAction = 'edit';

            if ($clone === true) {
                $triggerCampaign = clone $triggerCampaign;
                $objectAction = 'clone';
            }

            return $this->createTriggerCampaignModifyRequest(
                $triggerCampaign,
                $postActionVars,
                $this->generateUrl(self::ROUTES['action'], ['objectAction' => $objectAction, 'objectId' => $objectId]),
                $ignorePost
            );
        } catch (AccessDeniedException $exception) {
            return $this->accessDenied();
        } catch (EntityNotFoundException $exception) {
            return $this->postActionRedirect(array_merge($postActionVars, [
                'flashes' => [
                    [
                        'type' => 'error',
                        'msg' => 'plugin.triggerdialog.campaign.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ],
                ],
            ]));
        }
    }

    /**
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return Response
     */
    public function cloneAction($objectId, $ignorePost = false)
    {
        return $this->editAction($objectId, $ignorePost, true);
    }

    /**
     * @param int $objectId
     * @param mixed $batch
     *
     * @return Response
     */
    public function deleteAction($objectId, $batch = false)
    {
        $this->setSession();
        $page = $this->session->get(self::SESSION_VARS['page'], 1);
        $viewParameters = ['page' => $page];
        $returnUrl = $this->generateUrl(self::ROUTES['index'], $viewParameters);
        $flashes = [];

        $postActionVars = [
            'returnUrl' => $returnUrl,
            'viewParameters' => $viewParameters,
            'contentTemplate' => self::TEMPLATES['index'],
            'passthroughVars' => [
                'activeLink' => self::ACTIVE_LINK,
                'mauticContent' => self::MAUTIC_CONTENT,
            ],
        ];

        if ($this->request->getMethod() === 'POST') {
            if ($batch === true) {
                $this->deleteMultipleCampaigns($postActionVars, $flashes);
            } else {
                $response = $this->deleteSingleCampaign($objectId, $postActionVars, $flashes);
                if ($response !== null) {
                    return $response;
                }
            }
        }

        return $this->postActionRedirect(array_merge($postActionVars, [
            'flashes' => $flashes,
        ]));
    }

    /**
     * @return Response
     */
    public function batchDeleteAction()
    {
        return $this->deleteAction(0, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPermissions()
    {
        $security = $this->get('mautic.security');

        return $security->isGranted([
            self::PERMISSIONS['view'],
            self::PERMISSIONS['create'],
            self::PERMISSIONS['edit'],
            self::PERMISSIONS['delete'],
            self::PERMISSIONS['publish'],
        ], 'RETURN_ARRAY');
    }

    /**
     * @param CoreParametersHelper $coreParametersHelper
     *
     * @return bool
     */
    protected function checkConfiguration(CoreParametersHelper $coreParametersHelper)
    {
        if (!$coreParametersHelper->has('triggerdialog_rest_user') || empty($coreParametersHelper->get('triggerdialog_rest_user'))) {
            return false;
        }

        if (!$coreParametersHelper->has('triggerdialog_authenticationSecret') || empty($coreParametersHelper->get('triggerdialog_authenticationSecret'))) {
            return false;
        }

        return true;
    }

    protected function setSession()
    {
        $this->session = $this->get('session');
    }

    /**
     * @return CoreParametersHelper
     */
    protected function getCoreParametersHelper()
    {
        return $this->get('mautic.helper.core_parameters');
    }

    protected function getLimit()
    {
        return $this->session->get(self::SESSION_VARS['limit'], $this->coreParametersHelper->get('default_pagelimit'));
    }

    /**
     * @param int $limit
     * @param int $page
     *
     * @return float|int
     */
    protected function getStart($limit, $page)
    {
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);

        if ($start < 0) {
            $start = 0;
        }

        return $start;
    }

    /**
     * @return string
     */
    protected function getSearch()
    {
        $search = $this->request->get('search', $this->session->get(self::SESSION_VARS['search'], ''));
        $this->setSearch($search);

        return $search;
    }

    /**
     * @param string $search
     */
    protected function setSearch($search)
    {
        $this->session->set(self::SESSION_VARS['search'], $search);
    }

    /**
     * @param string $search
     *
     * @return array
     */
    protected function getFilter($search)
    {
        return [
            'string' => $search,
            'force' => [],
        ];
    }

    /**
     * @return string
     */
    protected function getOrderBy()
    {
        return $this->session->get(self::SESSION_VARS['orderBy'], TriggerCampaignRepository::ALIAS . '.name');
    }

    /**
     * @return string
     */
    protected function getOrderByDir()
    {
        return $this->session->get(self::SESSION_VARS['orderByDir'], 'ASC');
    }

    /**
     * @param string $filter
     * @param int    $start
     * @param int    $limit
     * @param string $orderBy
     * @param string $orderByDir
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    protected function getTriggerCampaigns($filter, $start, $limit, $orderBy, $orderByDir)
    {
        return $this->getModel(TriggerCampaignModel::NAME)
                    ->getEntities([
                        'start' => $start,
                        'limit' => $limit,
                        'filter' => $filter,
                        'orderBy' => $orderBy,
                        'orderByDir' => $orderByDir,
                    ]);
    }

    /**
     * @param int $count
     * @param int $limit
     *
     * @return Response
     */
    protected function redirectToLastPage($count, $limit)
    {
        $lastPage = ($count === 1) ? 1 : (ceil($count / $limit)) ?: 1;
        $this->session->set(self::SESSION_VARS['search'], $lastPage);
        $viewParameters = ['page' => $lastPage];

        return $this->postActionRedirect(
            [
                'returnUrl' => $this->generateUrl(self::ROUTES['index'], $viewParameters),
                'viewParameters' => $viewParameters,
                'contentTemplate' => self::TEMPLATES['index'],
                'passthroughVars' => [
                    'activeLink' => self::ACTIVE_LINK,
                    'mauticContent' => self::MAUTIC_CONTENT,
                ],
            ]
        );
    }

    /**
     * Set what page currently on so that we can return here after form submission/cancellation
     *
     * @param int $page
     */
    protected function setPage($page)
    {
        $this->session->set(self::SESSION_VARS['page'], $page);
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return  $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';
    }

    /**
     * @return string
     */
    protected function getSSOUrl(CoreParametersHelper $coreParametersHelper)
    {
        $ssoUtility = new SsoUtility($coreParametersHelper, $this->container->get('mautic.helper.user'));
        $ssoUrl = null;

        if ($ssoUtility->isValid()) {
            $ssoUtility->generateJWT();

            return $ssoUtility->getSSOUrl();
        }

        return '';
    }

    /**
     * Get variables for POST action.
     *
     * @return array
     */
    protected function getPostActionVars()
    {
        $viewParameters = ['page' => $this->session->get(self::SESSION_VARS['page'], 1)];

        return [
            'returnUrl' => $this->generateUrl(self::ROUTES['index'], $viewParameters),
            'viewParameters' => $viewParameters,
            'contentTemplate' => self::TEMPLATES['index'],
            'passthroughVars' => [
                'activeLink' => self::ACTIVE_LINK,
                'mauticContent' => self::MAUTIC_CONTENT,
            ],
        ];
    }

    /**
     * Return trigger campaign if exists and user has access.
     *
     * @param int $triggerCampaignId
     *
     * @throws EntityNotFoundException
     * @throws AccessDeniedException
     * @return TriggerCampaign
     */
    private function getTriggerCampaign($triggerCampaignId)
    {
        /** @var TriggerCampaign $triggerCampaign */
        $triggerCampaign = $this->getModel(TriggerCampaignModel::NAME)->getEntity($triggerCampaignId);

        // Check if exists
        if (!$triggerCampaign instanceof TriggerCampaign) {
            throw new EntityNotFoundException(sprintf('Trigger campaign with ID %d not found.', $triggerCampaignId), 1569248601);
        }

        if (!$this->get('mautic.security')->hasEntityAccess(true, self::PERMISSIONS['edit'], $triggerCampaign->getCreatedBy())) {
            throw new AccessDeniedException(sprintf('User has not access on segment with ID %d', $triggerCampaignId));
        }

        return $triggerCampaign;
    }

    /**
     * @param TriggerCampaign $triggerCampaign
     * @param array           $postActionVars
     * @param bool            $action
     * @param string          $ignorePost
     *
     * @return Response
     */
    protected function createTriggerCampaignModifyRequest(TriggerCampaign $triggerCampaign, array $postActionVars, $action, $ignorePost)
    {
        /** @var TriggerCampaignModel $triggerCampaignModel */
        $triggerCampaignModel = $this->getModel(TriggerCampaignModel::NAME);

        if ($triggerCampaignModel->isLocked($triggerCampaign)) {
            return $this->isLocked($postActionVars, $triggerCampaign, TriggerCampaignModel::NAME);
        }

        /** @var Form $form */
        $form = $triggerCampaignModel->createForm($triggerCampaign, $this->get('form.factory'), $action);

        // Check for a submitted form and process it
        if (!$ignorePost && $this->request->getMethod() === 'POST') {
            if ($this->isFormCancelled($form)) {
                $triggerCampaignModel->unlockEntity($triggerCampaign);

                return $this->postActionRedirect($postActionVars);
            }
            if ($this->isFormValid($form)) {
                $triggerCampaign->getChanges(false);
                //form is valid so process the data
                $triggerCampaignModel->saveEntity($triggerCampaign, $form->get('buttons')->get('save')->isClicked());

                $this->addFlash('mautic.core.notice.updated', [
                        '%name%' => $triggerCampaign->getName(),
                        '%menu_link%' => self::ROUTES['index'],
                        '%url%' => $this->generateUrl(self::ROUTES['action'], [
                            'objectAction' => 'edit',
                            'objectId' => $triggerCampaign->getId(),
                        ]),
                    ]);

                if ($form->get('buttons')->get('apply')->isClicked()) {
                    $contentTemplate = self::TEMPLATES['form'];
                    $postActionVars['contentTemplate'] = $contentTemplate;
                    $postActionVars['forwardController'] = false;
                    $postActionVars['returnUrl'] = $this->generateUrl(self::ROUTES['action'], [
                            'objectAction' => 'edit',
                            'objectId' => $triggerCampaign->getId(),
                        ]);

                    // Re-create the form once more with the fresh segment and action.
                    // The alias was empty on redirect after cloning.
                    $form = $triggerCampaignModel->createForm(
                        $triggerCampaign,
                        $this->get('form.factory'),
                        $this->generateUrl(
                            self::ROUTES['action'],
                            [
                                    'objectAction' => 'edit',
                                    'objectId' => $triggerCampaign->getId(),
                                ]
                        )
                    );

                    $postActionVars['viewParameters'] = [
                            'objectAction' => 'edit',
                            'objectId' => $triggerCampaign->getId(),
                            'form' => $this->setFormTheme($form, $contentTemplate, self::THEMES['variables']),
                        ];

                    return $this->postActionRedirect($postActionVars);
                } elseif ($form->get('buttons')->get('save')->isClicked()) {
                    return $this->postActionRedirect($postActionVars);
                }
            }
        } else {
            //lock the entity
            $triggerCampaignModel->lockEntity($triggerCampaign);
        }
        $test = $this->setFormTheme($form, self::TEMPLATES['form'], self::THEMES['variables']);

        return $this->delegateView([
            'viewParameters' => [
                'form' => $test,
                'currentListId' => $triggerCampaign->getId(),
            ],
            'contentTemplate' => self::TEMPLATES['form'],
            'passthroughVars' => [
                'activeLink' => self::ACTIVE_LINK,
                'route' => $action,
                'mauticContent' => self::MAUTIC_CONTENT,
            ],
        ]);
    }

    /**
     * @param array $postActionVars
     * @param array $flashes
     */
    protected function deleteMultipleCampaigns($postActionVars, &$flashes)
    {
        /** @var TriggerCampaignModel $triggerCampaignModel */
        $triggerCampaignModel = $this->getModel(TriggerCampaignModel::NAME);
        $triggerCampaignIds = json_decode($this->request->query->get('ids', '{}'));
        $deleteIds = [];

        // Loop over the IDs to perform access checks pre-delete
        foreach ($triggerCampaignIds as $triggerCampaignId) {
            $triggerCampaign = $triggerCampaignModel->getEntity($triggerCampaignId);

            if ($triggerCampaign === null) {
                $flashes[] = [
                    'type' => 'error',
                    'msg' => 'plugin.triggerdialog.campaign.error.notfound',
                    'msgVars' => ['%id%' => $triggerCampaignId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(true, self::PERMISSIONS['delete'], $triggerCampaign->getCreatedBy())) {
                $flashes[] = $this->accessDenied(true);
            } elseif ($triggerCampaignModel->isLocked($triggerCampaign)) {
                $flashes[] = $this->isLocked($postActionVars, $triggerCampaign, TriggerCampaignModel::NAME, true);
            } else {
                $deleteIds[] = $triggerCampaignId;
            }
        }

        // Delete everything we are able to
        if (!empty($deleteIds)) {
            $triggerCampaigns = $triggerCampaignModel->deleteEntities($deleteIds);

            $flashes[] = [
                'type' => 'notice',
                'msg' => 'plugin.triggerdialog.campaign.notice.batch_deleted',
                'msgVars' => [
                    '%count%' => count($triggerCampaigns),
                ],
            ];
        }
    }

    /**
     * @param int   $objectId
     * @param array $postActionVars
     * @param array $flashes
     *
     * @return Response
     */
    protected function deleteSingleCampaign($objectId, $postActionVars, &$flashes)
    {
        /** @var TriggerCampaignModel $triggerCampaignModel */
        $triggerCampaignModel = $this->getModel(TriggerCampaignModel::NAME);
        $triggerCampaign = $triggerCampaignModel->getEntity($objectId);

        if ($triggerCampaign === null) {
            $flashes[] = [
                'type' => 'error',
                'msg' => 'plugin.triggerdialog.campaign.error.notfound',
                'msgVars' => ['%id%' => $objectId],
            ];
        } elseif (!$this->get('mautic.security')->hasEntityAccess(true, self::PERMISSIONS['delete'], $triggerCampaign->getCreatedBy())) {
            return $this->accessDenied();
        } elseif ($triggerCampaignModel->isLocked($triggerCampaign)) {
            return $this->isLocked($postActionVars, $triggerCampaign, TriggerCampaignModel::NAME);
        }

        $triggerCampaignModel->deleteEntity($triggerCampaign);

        $flashes[] = [
            'type' => 'notice',
            'msg' => 'mautic.core.notice.deleted',
            'msgVars' => [
                '%name%' => $triggerCampaign->getName(),
                '%id%' => $objectId,
            ],
        ];

        return null;
    }
}
