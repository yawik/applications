<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** Applications controller */
namespace Applications\Controller;

use Applications\Listener\Events\ApplicationEvent;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Applications\Entity\StatusInterface as Status;
use Applications\Entity\Application;

/**
 * @method \Core\Controller\Plugin\Notification notification()
 * @method \Core\Controller\Plugin\Mailer mailer()
 * @method \Acl\Controller\Plugin\Acl acl()
 * @method \Auth\Controller\Plugin\Auth auth()
 *
 * Handles managing actions on applications
 */
class ManageController extends AbstractActionController
{
    /**
     * attaches further Listeners for generating / processing the output
     *
     * @return $this
     */
    public function attachDefaultListeners()
    {
        parent::attachDefaultListeners();
        $serviceLocator  = $this->serviceLocator;
        $defaultServices = $serviceLocator->get('DefaultListeners');
        $events          = $this->getEventManager();
        $events->attach($defaultServices);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractActionController::onDispatch()
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $action     = $this->params()->fromQuery('action');
        
        if ($routeMatch && $action) {
            $routeMatch->setParam('action', $action);
        }

        return parent::onDispatch($e);
    }
    
    /**
     * List applications
     */
    public function indexAction()
    {
        $services              = $this->serviceLocator;
        /* @var \Jobs\Repository\Job $jobRepository */
        $jobRepository         = $services->get('repositories')->get('Jobs/Job');
        /* @var \Applications\Repository\Application $applicationRepository */
        $applicationRepository = $services->get('repositories')->get('Applications/Application');
        $services_form         = $services->get('forms');
        /* @var \Applications\Form\FilterApplication $form */
        $form                  = $services_form->get('Applications/Filter');
        $params                = $this->getRequest()->getQuery();
        /* @var \Zend\Form\Element\Select $statusElement */
        $statusElement         = $form->get('status');

        $states                = $applicationRepository->getStates()->toArray();
        $states                = array_merge(array(/*@translate*/ 'all'), $states);
        
        $statesForSelections = array();
        foreach ($states as $state) {
            $statesForSelections[$state] = $state;
        }
        $statusElement->setValueOptions($statesForSelections);
        
        $job = $params->job ? $jobRepository->find($params->job)  : null;
        $paginator = $this->paginator('Applications');

        if ($job) {
            $params['job_title'] = '[' . $job->getApplyId() . '] ' . $job->getTitle();
        }

        $form->bind($params);
                
        return array(
            'form' => $form,
            'applications' => $paginator,
            'byJobs' => 'jobs' == $params->get('by', 'me'),
            'sort' => $params->get('sort', 'none'),
            'search' => $params->get('search', ''),
            'job' => $job,
            'applicationStates' => $states,
            'applicationState' => $params->get('status', '')
        );
    }

    /**
     * Detail view of an application
     *
     * @return array|JsonModel|ViewModel
     */
    public function detailAction()
    {
        if ('refresh-rating' == $this->params()->fromQuery('do')) {
            return $this->refreshRatingAction();
        }
        
        $nav = $this->serviceLocator->get('Core/Navigation');
        $page = $nav->findByRoute('lang/applications');
        $page->setActive();

        /* @var \Applications\Repository\Application$repository */
        $repository = $this->serviceLocator->get('repositories')->get('Applications/Application');
        /* @var Application $application */
        $application = $repository->find($this->params('id'));
        
        if (!$application) {
            $this->response->setStatusCode(410);
            $model = new ViewModel(
                array(
                'content' => /*@translate*/ 'Invalid apply id'
                )
            );
            $model->setTemplate('applications/error/not-found');
            return $model;
        }
        
        $this->acl($application, 'read');
        
        $applicationIsUnread = false;
        if ($application->isUnreadBy($this->auth('id')) && $application->getStatus()) {
            $application->addReadBy($this->auth('id'));
            $applicationIsUnread = true;
            $application->changeStatus(
                $application->getStatus(),
                sprintf(/*@translate*/ 'Application was read by %s',
                                       $this->auth()->getUser()->getInfo()->getDisplayName()));
        }


        
        $format=$this->params()->fromQuery('format');

        if ($application->isDraft()) {
            $list = false;
        } else {
            $list = $this->paginationParams('Applications\Index', $repository);
            $list->setCurrent($application->id);
        }

        $return = array(
            'application'=> $application,
            'list' => $list,
            'isUnread' => $applicationIsUnread,
            'format' => 'html'
        );
        switch ($format) {
            case 'json':
                /*@deprecated - must be refactored */
                        $viewModel = new JsonModel();
                        $viewModel->setVariables(
                            /*array(
                            'application' => */$this->serviceLocator
                                              ->get('builders')
                                              ->get('JsonApplication')
                                              ->unbuild($application)
                        );
                        $viewModel->setVariable('isUnread', $applicationIsUnread);
                        $return = $viewModel;
                break;
            case 'pdf':
                $pdf = $this->serviceLocator->get('Core/html2pdf');
                $return['format'] = $format;
                break;
            default:
                $contentCollector = $this->getPluginManager()->get('Core/ContentCollector');
                $contentCollector->setTemplate('applications/manage/details/action-buttons');
                $actionButtons = $contentCollector->trigger('application.detail.actionbuttons', $application);
                
                $return = new ViewModel($return);
                $return->addChild($actionButtons, 'externActionButtons');
                break;
        }
        
        return $return;
    }
    
    /**
     * Refreshes the rating of an application
     *
     * @throws \DomainException
     * @return \Zend\View\Model\ViewModel
     */
    public function refreshRatingAction()
    {
        $model = new ViewModel();
        $model->setTemplate('applications/manage/_rating');
        
        $application = $this->serviceLocator->get('repositories')->get('Applications/Application')
                        ->find($this->params('id', 0));
        
        if (!$application) {
            throw new \DomainException('Invalid application id.');
        }
        
        $model->setVariable('application', $application);
        return $model;
    }
    
    /**
     * Attaches a social profile to an application
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function socialProfileAction()
    {
        if ($spId = $this->params()->fromQuery('spId')) {
            $repositories = $this->serviceLocator->get('repositories');
            $repo = $repositories->get('Applications/Application');
            $profile = $repo->findProfile($this->params()->fromQuery('spId'));
            if (!$profile) {
                throw new \InvalidArgumentException('Could not find profile.');
            }
        } elseif ($this->getRequest()->isPost()
                   && ($network = $this->params()->fromQuery('network'))
                   && ($data    = $this->params()->fromPost('data'))
        ) {
            $profileClass = '\\Auth\\Entity\\SocialProfiles\\' . $network;
            $profile      = new $profileClass();
            $profile->setData(\Zend\Json\Json::decode($data, \Zend\Json\Json::TYPE_ARRAY));
        } else {
            throw new \RuntimeException(
                'Missing arguments. Either provide "spId" as Get or "network" and "data" as Post.'
            );
        }
        
        return array(
            'profile' => $profile
        );
    }

    /**
     * Changes the status of an application
     *
     * @return array
     */
    public function statusAction()
    {
        $applicationId = $this->params('id');
        /* @var \Applications\Repository\Application $repository */
        $repository    = $this->serviceLocator->get('repositories')->get('Applications/Application');
        /* @var Application $application */
        $application   = $repository->find($applicationId);

        /* @var Request $request */
        $request = $this->getRequest();

        if (!$application) {
            throw new \InvalidArgumentException('Could not find application.');
        }
        
        $this->acl($application, 'change');
        
        $jsonFormat    = 'json' == $this->params()->fromQuery('format');
        $status        = $this->params('status', Status::CONFIRMED);
        $settings = $this->settings();
        
        if (in_array($status, array(Status::INCOMING))) {
            $application->changeStatus($status);
            if ($request->isXmlHttpRequest()) {
                $response = $this->getResponse();
                $response->setContent('ok');
                return $response;
            }
            if ($jsonFormat) {
                return array(
                    'status' => 'success',
                );
            }
            return $this->redirect()->toRoute('lang/applications/detail', array(), true);
        }

        $events = $this->serviceLocator->get('Applications/Events');

        /* @var ApplicationEvent $event */
        $event = $events->getEvent(ApplicationEvent::EVENT_APPLICATION_STATUS_CHANGE,
                                   $this,
                                   [
                                       'application' => $application,
                                       'status' => $status,
                                       'user' => $this->auth()->getUser(),
                                   ]
        );

        $event->setIsPostRequest($request->isPost());
        $event->setPostData($request->getPost());
        $events->trigger($event);

        $params = $event->getFormData();


        if ($request->isPost()) {

            if ($jsonFormat) {
                return array(
                    'status' => 'success',
                );
            }
            $this->notification()->success($event->getNotification());
            return $this->redirect()->toRoute('lang/applications/detail', array(), true);
        }

        if ($jsonFormat) {
            return $params;
        }

        /* @var \Applications\Form\Mail $form */
        $form = $this->serviceLocator->get('FormElementManager')->get('Applications/Mail');
        $form->populateValues($params);



        $reciptient = $params['to'];

        return [
            'recipient' => $reciptient,
            'form' => $form
        ];
    }
    
    /**
     * Forwards an application via Email
     *
     * @throws \InvalidArgumentException
     * @return \Zend\View\Model\JsonModel
     */
    public function forwardAction()
    {
        $services     = $this->serviceLocator;
        $emailAddress = $this->params()->fromQuery('email');
        /* @var \Applications\Entity\Application $application */
        $application  = $services->get('repositories')->get('Applications/Application')
                                 ->find($this->params('id'));
        
        $this->acl($application, 'forward');
        
        $translator   = $services->get('translator');
         
        if (!$emailAddress) {
            throw new \InvalidArgumentException('An email address must be supplied.');
        }
        
        $params = array(
            'ok' => true,
            'text' => sprintf($translator->translate('Forwarded application to %s'), $emailAddress)
        );
        
        try {
            $userName    = $this->auth('info')->displayName;
            $fromAddress = $application->getJob()->getContactEmail();
            $mailOptions = array(
                'application' => $application,
                'to'          => $emailAddress,
                'from'        => array($fromAddress => $userName)
            );
            $this->mailer('Applications/Forward', $mailOptions, true);
            $this->notification()->success($params['text']);
        } catch (\Exception $ex) {
            $params = array(
                'ok' => false,
                'text' => sprintf($translator->translate('Forward application to %s failed.'), $emailAddress)
            );
            $this->notification()->error($params['text']);
        }
        $application->changeStatus($application->getStatus(), $params['text']);
        return new JsonModel($params);
    }

    /**
     * Deletes an application
     *
     * @return array|\Zend\Http\Response
     */
    public function deleteAction()
    {
        $id          = $this->params('id');
        $services    = $this->serviceLocator;
        $repositories= $services->get('repositories');
        $repository  = $repositories->get('Applications/Application');
        $application = $repository->find($id);
        
        if (!$application) {
            throw new \DomainException('Application not found.');
        }

        $this->acl($application, 'delete');

        $events   = $services->get('Applications/Events');
        $events->trigger(ApplicationEvent::EVENT_APPLICATION_PRE_DELETE, $this, [ 'application' => $application ]);
        
        $repositories->remove($application);
        
        if ('json' == $this->params()->fromQuery('format')) {
            return ['status' => 'success'];
        }
        
        return $this->redirect()->toRoute('lang/applications', array(), true);
    }

    /**
     * Move an application to talent pool
     *
     * @return \Zend\Http\Response
     * @since 0.26
     */
    public function moveAction()
    {
        $id = $this->params('id');
        $serviceManager = $this->serviceLocator;
        $repositories = $serviceManager->get('repositories');
        $application = $repositories->get('Applications/Application')->find($id);
        
        if (!$application) {
            throw new \DomainException('Application not found.');
        }

        $this->acl($application, 'move');
        
        $user = $this->auth()->getUser();
        $cv = $repositories->get('Cv/Cv')->createFromApplication($application, $user);
        
        $repositories->store($cv);
        $repositories->remove($application);

        $this->notification()->success(
            /*@translate*/ 'Application has been successfully moved to Talent Pool');
        
        return $this->redirect()->toRoute('lang/applications', array(), true);
    }
}
