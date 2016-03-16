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

use Jobs\Entity\JobInterface;
use Core\Mail\StringTemplateMessage;

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

        $userInfo = $this->user->getInfo();
        $name = $userInfo->getDisplayName();
        if ('' == trim($name)) {
            $name = $userInfo->getEmail();
        }
        
        $variables = array(
            'name' => $name,
            'title' => $this->job->getTitle()
        );
        
        $this->setTo($userInfo->getEmail(), $name != $userInfo->getEmail() ? $name : null);

        if ($this->admin && $this->admin->getSettings('Applications')->getMailBCC()) {
            $this->addBcc($this->admin->info->email, $this->admin->info->displayName);
        }

        $this->setVariables($variables);
        $subject = /*@translate*/ 'New application for your vacancy "%s"';

        if ($this->isTranslatorEnabled()) {
            $subject = $this->getTranslator()->translate($subject);
        }
        $this->setSubject(sprintf($subject, $this->job->getTitle()));
        
        /* @todo settings retrieved from user entity is an array
         *       not an entity.
         */
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
}