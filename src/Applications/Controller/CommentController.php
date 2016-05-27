<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** CommentController.php */
namespace Applications\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Applications\Entity\Comment;
use Zend\View\Model\ViewModel;

/**
 * Controls comment handling on applications
 *
 * @method \Auth\Controller\Plugin\Auth auth()
 */
class CommentController extends AbstractActionController
{
    /**
     * attaches further Listeners for generating / processing the output
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
    public function onDispatch(MvcEvent $event)
    {
        $request = $event->getRequest();
        if (!$request instanceof HttpRequest || !$request->isXmlHttpRequest()) {
            //throw new \RuntimeException('This controller must only be called with ajax requests.');
        }
        return parent::onDispatch($event);
    }
    
    /**
     * Lists comments of an application
     *
     * @return multitype:NULL
     */
    public function listAction()
    {
        $repository = $this->serviceLocator->get('repositories')->get('Applications/Application');
        $applicationId = $this->params()->fromQuery('applicationId', 0);
        $application = $repository->find($applicationId);
        
        $this->acl($application, 'read');
        
        return array(
            'comments' => $application->comments,
        );
    }
    
    /**
     * Processes formular data
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function formAction()
    {
        $services = $this->serviceLocator;
        $repository = $services->get('repositories')->get('Applications/Application');
        
        $mode  = $this->params()->fromQuery('mode', 'new');
        $appId = $this->params()->fromQuery('id');
        /* @var \Applications\Entity\Application $application */
        $application = $repository->find($appId);

        $viewModel = new ViewModel();
        if ('edit' == $mode) {
            $comment = $repository->findComment($appId);
        } else {
            $comment = new Comment();
            $comment->setUser($this->auth()->getUser());
        }
        
        $this->acl($application, 'read');
        
        $form = $services->get('forms')->get('Applications/CommentForm');
        $form->bind($comment);
        
        if ($this->getRequest()->isPost()) {
            $form->setData($_POST);
            
            if ($form->isValid()) {
                if ('new' == $mode) {
                    $application = $repository->find($appId);
                    $application->comments->add($comment);
                    $application->changeStatus($application->getStatus(), sprintf(
                                    /* @translate */ 'Application was rated by %s',
                                     $this->auth()->getUser()->getInfo()->getDisplayName())
                        );
                }
                $viewModel->setVariable('isSaved', true);
            }
        }
       
        $viewModel->setVariables(
            array(
            'mode' => $mode,
            'identifier' => $appId,
            'commentForm' => $form,
            )
        );
        return $viewModel;
    }
}
