<?php

/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** FilterApplication.php */

namespace Applications\Form;

use Zend\Form\Form;
use Zend\Hydrator\ArraySerializable as ArrayHydrator;

/**
 * Formular to search for applications
 *
 * Class FilterApplication
 * @package Applications\Form
 */
class FilterApplication extends Form
{

    protected $hydrator;

    public function getHydrator()
    {
        if (!$this->hydrator) {
            $hydrator = new ArrayHydrator();
            $this->setHydrator($hydrator);
        }
        return $this->hydrator;
    }

    /**
     * initialize filter form
     */
    public function init()
    {
        $this->setName('search-applications-form')
                ->setLabel('Search applications')
                ->setAttributes(
                    array(
                    'class' => 'form-inline',
                    'method' => 'get')
                );

        $this->add(
            array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'search',
            'options' => array(
                'label' => /* @translate */ 'Search'
            ),
            )
        );

        $this->add(
            array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'job_title',
            'options' => array(
                'label' => /* @translate */ 'Enter job title',
            ),
            'attributes' => array(
                'id' => 'job-filter',
            )
            )
        );

        $this->add(
            array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'job',
            'attributes' => array(
                'id' => 'job-filter-value',
            )
            )
        );

        $this->add(
            array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'status',
            'options' => array(
                'label' => /* @translate */ 'Status'
            ),
            )
        );

        
        $this->add(
            array('type' => 'ToggleButton',
            'name' => 'unread',
            'options' => array(
                'checked_value' => '1',
                'unchecked_value' => '0',
                'label' => 'unread',
            )
            )
        );

        $this->add(
            array(
            'type' => 'Zend\Form\Element\Button',
            'name' => 'submit',
            'attributes' => array(
                'value' => "1",
                'type' => 'submit',
                'class' => 'btn btn-primary'
                ),
            'options' => array(
                'label' => /* @translate */ 'Search'
            ),
            )
        );
        
        $this->add(
            array(
            'type' => 'href',
            'name' => 'clear',
            'options' => array(
                'label' => /* @translate */ 'Clear'
            ),
            'attributes' => array(
                'class' => 'btn btn-default',
                //'onClick' => 'window.location.href=\'' . $options['clearRef'] . '\''
                'onClick' => 'window.location.href=\'?clear=1\''
            ),
            )
        );
    }
}
