<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** NewApplication.php */
namespace Applications\Mail;

use Auth\Entity\UserInterface;
use Jobs\Entity\JobInterface;
use Core\Mail\StringTemplateMessage;
use Organizations\Entity\EmployeeInterface;

/**
 * Sends Information about a new Application to the recruiter
 *
 * Class NewApplication
 * @package Applications\Mail
 */
class NewApplication extends StringTemplateMessage
{
    /**
     * Job posting
     *
     * @var \Jobs\Entity\Job $job
     */
    protected $job;

    /**
     * Owner of the job posting
     *
     * @var \Auth\Entity\User $user
     */
    protected $user;

    /**
     * Organization Admin
     *
     * @var bool|\Auth\Entity\User $admin
     */
    protected $admin;

    /**
     * @var bool
     */
    private $callInitOnSetJob = false;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->callInitOnSetJob = true;
    }
    
    public function init()
    {
        if (!$this->job) {
            return false;
        }

        /* @var \Auth\Entity\Info $userInfo */
        $userInfo = $this->user->getInfo();
        $name = $userInfo->getDisplayName();
        if ('' == trim($name)) {
            $name = $userInfo->getEmail();
        }
        
        $variables = array(
            'name' => $name,
            'title' => $this->job->getTitle()
        );

        $this->setReciptient();

        $this->setVariables($variables);
        $subject = /*@translate*/ 'New application for your vacancy "%s"';

        if ($this->isTranslatorEnabled()) {
            $subject = $this->getTranslator()->translate($subject);
        }
        $this->setSubject(sprintf($subject, $this->job->getTitle()));
        
        /* @var \Applications\Entity\Settings $settings */
        $settings = $this->user->getSettings('Applications');

        $body = $settings->getMailAccessText();
        if ('' == $body) {
            $body = /*@translate*/ "Hello ##name##,\n\nThere is a new application for your vacancy:\n\"##title##\"\n\n";
            if ($this->isTranslatorEnabled()) {
                $body = $this->getTranslator()->translate($body);
            }
        }
        
        $this->setBody($body);
        return $this;
    }

    /**
     * @param JobInterface $job
     * @param bool $init
     * @return $this
     */
    public function setJob(JobInterface $job, $init = true)
    {
        $this->job = $job;
        if ($this->callInitOnSetJob) {
            $this->init();
        }
        return $this;
    }

    /**
     * @param \Auth\Entity\User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user=$user;
        return $this;
    }

    /**
     * @param \Auth\Entity\User $admin
     * @return $this
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
        return $this;
    }

    protected function setReciptient() {
        $workflowSettings = $this->getWorkflowSettings();
        if ($workflowSettings->getAcceptApplicationByDepartmentManager()){
            $departmentManagers = $this->getDepartmentManagers();
            foreach ($departmentManagers as $employee) { /* @var \Organizations\Entity\Employee $employee */
                $this->addTo($employee->getUser()->getInfo()->getEmail(), $employee->getUser()->getInfo()->getDisplayName());
            }
        } else {
            $this->setTo($this->user->getInfo()->getEmail(), $this->user->getInfo()->getDisplayName());
        }
        if (true === $this->admin && $this->user->getSettings('Applications')->getMailBCC()) {
            $this->addBcc($this->user->getInfo()->getEmail(), $this->user->getInfo()->getDisplayName());
        } elseif($this->admin instanceof UserInterface && $this->admin->getSettings('Applications')->getMailBCC()) {
            $this->addBcc($this->admin->getInfo()->getEmail(), $this->admin->getInfo()->getDisplayName());
        }
    }

    /**
     * @return bool|\Doctrine\Common\Collections\Collection
     */
    protected function getDepartmentManagers(){
        if (true === $this->admin) {
            return $this->user->getOrganization()->getOrganization()->getEmployeesByRole(EmployeeInterface::ROLE_DEPARTMENT_MANAGER);
        } elseif($this->admin) {
            return $this->admin->getOrganization()->getOrganization()->getEmployeesByRole(EmployeeInterface::ROLE_DEPARTMENT_MANAGER);
        } else {
            return false;
        }
    }

    /**
     * @return bool|\Organizations\Entity\WorkflowSettings
     */
    protected function getWorkflowSettings()
    {
        if ($this->user->getOrganization()->hasAssociation()) {
            return $this->user->getOrganization()->getOrganization()->getWorkflowSettings();
        } else {
            return false;
        }
    }

}
