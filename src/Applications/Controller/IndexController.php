<?php
/**
 * Cross Applicant Management
 * 
 * @filesource
 * @copyright (c) 2013 Cross Solution (http://cross-solution.de)
 * @license   GPLv3
 */

/** Applications controller */
namespace Applications\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Applications\Entity\Status;

/**
 * Main Action Controller for Applications module.
 *
 */
class IndexController extends AbstractActionController
{
    
    /**
     * Main apply site
     *
     */
    public function indexAction()
    { 
//         $view = new ViewModel();
//         $view->setTerminal(true);
//         return $view;
        //$this->layout('layout/apply');
        
        
        $services = $this->getServiceLocator();
        $request = $this->getRequest();
        
        list($jobId,$applyId) = array($this->params()->fromPost('jobId',0), (int) $this->params()->fromPost('applyId',0));
        $job = ($request->isPost() && !empty($jobId))
             ? $services->get('repositories')->get('job')->find($jobId)
             : $services->get('repositories')->get('job')->findByApplyId((0 == $applyId)?$this->params('jobId'):$applyId);
        
        
        $form = $this->getServiceLocator()->get('FormElementManager')->get('Application');
        
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'job' => $job,
            'form' => $form,
            'isApplicationSaved' => false,
        ));
        $applicationEntity = $services->get('builders')->get('Application')->getEntity();
        if ($this->auth()->isLoggedIn()) {
            $applicationEntity->setContact(clone $this->auth()->get('info')->getEntity());
        }
        $applicationEntity->injectJob($job);
        $form->bind($applicationEntity);
       
        if ($request->isPost()) {
            if ($returnTo = $this->params()->fromPost('returnTo', false)) {
                $returnTo = \Zend\Uri\UriFactory::factory($returnTo);
            }
            $services = $this->getServiceLocator();
            $repository = $services->get('repositories')->get('Application');
            
            
            //$applicationEntity = $services->get('builders')->get('Application')->getEntity(); 
            //$form->bind($applicationEntity);
            $data = array_merge_recursive(
                $this->request->getPost()->toArray(),
                $this->request->getFiles()->toArray()
            );
            
            $form->setData($data);
            
            if (!$form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonModel(array(
                        'ok' => false,
                        'messages' => $form->getMessages()
                    ));
                }
                if ($returnTo) {
                    $returnTo->setQuery($returnTo->getQueryAsArray() + array('status' => 'failure'));
                    return $this->redirect()->toUrl((string) $returnTo);
                }
                //$form->populateValues($data);
            } else {
                $auth = $this->auth();
            
                if ($auth->isLoggedIn()) {
                    $applicationEntity->setUserId($auth('id'));
                }
                $applicationEntity->setStatus(new Status());
                //$applicationEntity->injectJob($job);
                $image = $applicationEntity->getContact()->getImage();
                
                
                    
                if (!$image && $this->auth()->isLoggedIn()) {
                    $user = $this->auth()->getUser();
                    $userImage = clone $user->info->image;
                    $userImage->addAllowedUser($job->userId);
                    $applicationEntity->contact->setImage($fileRepository->saveCopy($userImage));
                }
                
                
                $repository->save($applicationEntity);
                
                /*
                 * New Application alert Mails to job owner
                 */
                $settings = $this->settings($job->user->getEntity()); 
                if ($email = $job->getContactEmail()) {
                    $this->mailer('Applications/NewApplication', array('job' => $job), /*sendMail*/ true);
                }
                
                if ($this->auth()->isLoggedIn()) {
                    $userInfo = $this->auth()->get('info')->getEntity();
                    if (isset($userInfo)) {
                        // TODO: will dieser User eine Info haben (aus den Settings lesen)
                        $email = $userInfo->getEmail();
                        if (isset($email)) {
                            $userRel = $job->getUser();
                            //$user = $userRel->getEntity();
                            //$settings = $this->settings('auth', $user);
                            $settingsJobAuth = $this->settings('auth', $job->getUserid());
                            if (isset($settingsJobAuth->mailText)) {
                                $mail = $this->mail();
                                $mail->addTo($email);
                                $mail->setBody($settingsJobAuth->mailText);
                                $mail->setFrom('cross@cross-solution.de', 'Cross Applicant Management');
                                $mail->setSubject('Bestätigung Bewerbung');
                                $result = $mail->send();
                            }  
                        }
                    }
                }
                    
                if ($request->isXmlHttpRequest()) {
                    return new JsonModel(array(
                        'ok' => true,
                        'id' => $applicationEntity->id,
                        'jobId' => $applicationEntity->jobId,
                    ));
                }
                if ($returnTo) {
                    $returnTo->setQuery($returnTo->getQueryAsArray() + array('status' => 'success'));
                    return $this->redirect()->toUrl((string) $returnTo);
                }
                $viewModel->setVariable('isApplicationSaved', true);
            }
        } 
        return $viewModel;

    }
}

