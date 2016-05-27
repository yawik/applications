<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** Forward.php */

namespace Applications\Mail;

use Applications\Entity\Application;
use Core\Mail\TranslatorAwareMessage;
use Zend\Mime;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
* Sends an e-mail containing an applications
*/

class Forward extends TranslatorAwareMessage
{
    /**
     * @var Application
     */
    protected $application;
    /**
     * @var bool
     */
    protected $isInitialized = false;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param $application
     * @return $this
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
        if ($this->isInitialized) {
            $this->generateBody();
        }
        return $this;
    }
    
    public function init()
    {
        $this->isInitialized = true;
        if (!$this->application) {
            return;
        }
        $this->setEncoding('UTF-8');
        $subject = /* @translate */ 'Fwd: Application to "%s" dated %s';
        if ($this->isTranslatorEnabled()) {
            $subject = $this->getTranslator()->translate($subject);
        }
        $this->setSubject(
            sprintf(
                $subject,
                $this->application->getJob()->getTitle(),
                strftime('%x', $this->application->getDateCreated()->getTimestamp())
            )
        );
        $this->generateBody();
    }

    /**
     * Generates the Mail Body
     */
    protected function generateBody()
    {
        $message = new Mime\Message();

        $text = $this->generateHtml();
        $textPart = new Mime\Part($text);
        $textPart->type = 'text/html';
        $textPart->charset = 'UTF-8';
        $textPart->disposition = Mime\Mime::DISPOSITION_INLINE;
        $message->addPart($textPart);

        if (isset($this->application->getContact()->image) &&
            $this->application->getContact()->getImage()->id) {
            /* @var $image \Auth\Entity\UserImage */
            $image = $this->application->getContact()->getImage();
            $part = new Mime\Part($image->getResource());
            $part->type = $image->type;
            $part->encoding = Mime\Mime::ENCODING_BASE64;
            $part->filename = $image->name;
            $part->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
            $message->addPart($part);
        }
        
        foreach ($this->application->getAttachments() as $attachment) {
            /* @var $part \Applications\Entity\Attachment */
            $part = new Mime\Part($attachment->getResource());
            $part->type = $attachment->type;
            $part->encoding = Mime\Mime::ENCODING_BASE64;
            $part->filename = $attachment->name;
            $part->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
            $message->addPart($part);
        }
        
        $this->setBody($message);
    }

    /**
     * Generates a mail containing an Application.
     *
     * @return mixed
     */
    protected function generateHtml()
    {
        $services = $this->getServiceLocator()->getServiceLocator();

         /*
          * "ViewHelperManager" defined by ZF2
          *  see http://framework.zend.com/manual/2.0/en/modules/zend.mvc.services.html#viewmanager
          */
         $viewManager = $services->get('ViewHelperManager');

        return $viewManager->get("partial")->__invoke('applications/mail/forward', array("application"=>$this->application));
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Applications\Mail\Forward
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
