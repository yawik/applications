<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2104 Cross Solution (http://cross-solution.de)
 * @license   GPLv3
 */

namespace Applications\Form;

use Core\Entity\Hydrator\EntityHydrator;
use Applications\Entity\Attachment;
use Applications\Entity\Cv;
use Applications\Entity\Contact;
use Core\Form\Form;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Core\Entity\Collection\ArrayCollection;
use Core\Entity\EntityInterface;

/**
 * create an application form.
 */
class CreateApplication extends Form implements ServiceLocatorAwareInterface
{
    protected $forms;
    protected $inputFilterSpecification;
    protected $preferFormInputFilter = true;
    protected $isInitialized;
    
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->forms = $serviceLocator;
        return $this;
    }
    
    public function getServiceLocator()
    {
        return $this->forms;
    }
    
    /*
     * hydrating strategies are defined by doctrine annotations
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
             $this->setHydrator(new EntityHydrator());
        }
        return $this->hydrator;
    }
    
    public function setObject($object)
    {
        parent::setObject($object);
        if (!$this->isInitialized) {
            $this->initLazy();
            $this->isInitialized = true;
        }
        $this->get('base')->setObject($object);
        return $this;
    }
    
    public function initLazy()
    {
        $this->setName('create-application-form');
        
        $this->initDispositive();
      
        $this->add($this->forms
                         ->get('Applications/ContactFieldset')
                         ->setLabel('personal informations')
                         ->setName('contact')
                         ->setObject(new Contact()));
        
        
        $this->add($this->forms->get('Applications/BaseFieldset'));

        /**
         * ads a cv section to the application formular
         */
        
        $this->add(
            $this->forms->get('CvFieldset')->setObject(new Cv())
        );
        
        $this->add(array(
            'type' => 'Auth/SocialProfilesFieldset',
            'name' => 'profiles',
            'options' => array(
                'profiles' => array(
                    'facebook' => 'Facebook',
                    'xing'     => 'Xing',
                ),
            ),
        ));
        
        $attachments = $this->forms->get('Applications/AttachmentsCollection');
        $this->add(
            $attachments
        );
        
        /**
         * sends a Carbon-Copy to the Applicant
         */
        $this->add(
            $this->forms->get('Applications/CarbonCopy')
        );
        
        /**
         * adds the privacy policy to the application fomular
         */
        //$this->add(
        //    $this->forms->get('Applications/Privacy')
        //);
        $applicationsPrivacy = $this->forms->get('Applications/PrivacyPolicy');
        
        $this->add(
            $applicationsPrivacy
        );
        
        $buttons = $this->forms->get('DefaultButtonsFieldset');
        $buttons->get('submit')->setLabel( /* @translate */ 'send application');
        $this->add($buttons);
        
        //$this->setValidationGroup('jobId', 'contact', 'base', 'cv');
       
    }
    
    public function initDispositive()
    {
         $this->add(array(
            'type' => 'hidden',
            'name' => 'jobId',
            'required' => true
        ));
        
        $subscriber = $this->add(array(
            'type' => 'hidden',
            'name' => 'subscriberUri'
        ));
    }
}