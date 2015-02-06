<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2015 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/**  */ 
namespace Applications\Form;

use Core\Form\Form;
/**
 * Form holds some application specific attributes.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class Attributes extends Form
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->setIsDisableCapable(false)
             ->setIsDisableElementsCapable(false)
             ->setAttribute('data-submit-on', 'checkbox');

        $this->add(array(
            'type' => 'checkbox',
            'name' => 'sendCarbonCopy',
            'options' => array(
                'headline' => /*@translate*/ 'Carbon Copy',
                'long_label' => /*@translate*/ 'send me a carbon copy of my application'
            ),
            'attributes' => array(
                'data-validate' => 'sendCarbonCopy',
                'data-trigger'  => 'submit',
            ),
        ));
        
        $this->add(array(
            'type' => 'infocheckbox',
            'name' => 'acceptedPrivacyPolicy',
            'options' => array(
                'headline' => /*@translate*/ 'Privacy Policy',
                'long_label' => /*@translate*/ 'I have read the %s and accept it',
                'linktext' => /*@translate*/ 'Privacy Policy',
                'route' => 'lang/content',
                'params' => array(
                    'view' => 'applications-privacy-policy'
                 )
            ),
            'attributes' => array(
                'data-validate' => 'acceptedPrivacyPolicy',
                'data-trigger' => 'submit',
            ),
        ));
    }
}
