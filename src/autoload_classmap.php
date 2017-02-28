<?php
// Generated by ZF2's ./bin/classmap_generator.php
return array(
    'Applications\Acl\ApplicationAccessAssertion'                      => __DIR__ . '/Applications/Acl/ApplicationAccessAssertion.php',
    'Applications\Auth\Dependency\ListListener'                        => __DIR__ . '/Applications/Auth/Dependency/ListListener.php',
    'Applications\Controller\ApplyController'                          => __DIR__ . '/Applications/Controller/ApplyController.php',
    'Applications\Controller\CommentController'                        => __DIR__ . '/Applications/Controller/CommentController.php',
    'Applications\Controller\ConsoleController'                        => __DIR__ . '/Applications/Controller/ConsoleController.php',
    'Applications\Controller\IndexController'                          => __DIR__ . '/Applications/Controller/IndexController.php',
    'Applications\Controller\ManageController'                         => __DIR__ . '/Applications/Controller/ManageController.php',
    'Applications\Controller\MultimanageController'                    => __DIR__ . '/Applications/Controller/MultimanageController.php',
    'Applications\Controller\Plugin\StatusChanger'                     => __DIR__ . '/Applications/Controller/Plugin/StatusChanger.php',
    'Applications\Entity\Application'                                  => __DIR__ . '/Applications/Entity/Application.php',
    'Applications\Entity\ApplicationInterface'                         => __DIR__ . '/Applications/Entity/ApplicationInterface.php',
    'Applications\Entity\Attachment'                                   => __DIR__ . '/Applications/Entity/Attachment.php',
    'Applications\Entity\Attributes'                                   => __DIR__ . '/Applications/Entity/Attributes.php',
    'Applications\Entity\Comment'                                      => __DIR__ . '/Applications/Entity/Comment.php',
    'Applications\Entity\CommentInterface'                             => __DIR__ . '/Applications/Entity/CommentInterface.php',
    'Applications\Entity\Contact'                                      => __DIR__ . '/Applications/Entity/Contact.php',
    'Applications\Entity\Cv'                                           => __DIR__ . '/Applications/Entity/Cv.php',
    'Applications\Entity\Facts'                                        => __DIR__ . '/Applications/Entity/Facts.php',
    'Applications\Entity\FactsInterface'                               => __DIR__ . '/Applications/Entity/FactsInterface.php',
    'Applications\Entity\History'                                      => __DIR__ . '/Applications/Entity/History.php',
    'Applications\Entity\HistoryInterface'                             => __DIR__ . '/Applications/Entity/HistoryInterface.php',
    'Applications\Entity\InternalReferences'                           => __DIR__ . '/Applications/Entity/InternalReferences.php',
    'Applications\Entity\MailHistory'                                  => __DIR__ . '/Applications/Entity/MailHistory.php',
    'Applications\Entity\MailHistoryInterface'                         => __DIR__ . '/Applications/Entity/MailHistoryInterface.php',
    'Applications\Entity\Rating'                                       => __DIR__ . '/Applications/Entity/Rating.php',
    'Applications\Entity\RatingInterface'                              => __DIR__ . '/Applications/Entity/RatingInterface.php',
    'Applications\Entity\Settings'                                     => __DIR__ . '/Applications/Entity/Settings.php',
    'Applications\Entity\SettingsInterface'                            => __DIR__ . '/Applications/Entity/SettingsInterface.php',
    'Applications\Entity\Status'                                       => __DIR__ . '/Applications/Entity/Status.php',
    'Applications\Entity\StatusInterface'                              => __DIR__ . '/Applications/Entity/StatusInterface.php',
    'Applications\Entity\Subscriber'                                   => __DIR__ . '/Applications/Entity/Subscriber.php',
    'Applications\Entity\SubscriberInterface'                          => __DIR__ . '/Applications/Entity/SubscriberInterface.php',
    'Applications\Entity\Validator\Application'                        => __DIR__ . '/Applications/Entity/Validator/Application.php',
    'Applications\Factory\Auth\Dependency\ListListenerFactory'         => __DIR__ . '/Applications/Factory/Auth/Dependency/ListListenerFactory.php',
    'Applications\Factory\Form\AttachmentsFactory'                     => __DIR__ . '/Applications/Factory/Form/AttachmentsFactory.php',
    'Applications\Factory\Form\ContactImageFactory'                    => __DIR__ . '/Applications/Factory/Form/ContactImageFactory.php',
    'Applications\Factory\Listener\EventApplicationCreatedFactory'     => __DIR__ . '/Applications/Factory/Listener/EventApplicationCreatedFactory.php',
    'Applications\Factory\Listener\StatusChangeFactory'                => __DIR__ . '/Applications/Factory/Listener/StatusChangeFactory.php',
    'Applications\Factory\ModuleOptionsFactory'                        => __DIR__ . '/Applications/Factory/ModuleOptionsFactory.php',
    'Applications\Filter\ActionToStatus'                               => __DIR__ . '/Applications/Filter/ActionToStatus.php',
    'Applications\Form\Apply'                                          => __DIR__ . '/Applications/Form/Apply.php',
    'Applications\Form\Attributes'                                     => __DIR__ . '/Applications/Form/Attributes.php',
    'Applications\Form\Base'                                           => __DIR__ . '/Applications/Form/Base.php',
    'Applications\Form\BaseFieldset'                                   => __DIR__ . '/Applications/Form/BaseFieldset.php',
    'Applications\Form\CarbonCopyFieldset'                             => __DIR__ . '/Applications/Form/CarbonCopyFieldset.php',
    'Applications\Form\CommentForm'                                    => __DIR__ . '/Applications/Form/CommentForm.php',
    'Applications\Form\ContactContainer'                               => __DIR__ . '/Applications/Form/ContactContainer.php',
    'Applications\Form\Element\Ref'                                    => __DIR__ . '/Applications/Form/Element/Ref.php',
    'Applications\Form\Facts'                                          => __DIR__ . '/Applications/Form/Facts.php',
    'Applications\Form\FactsFieldset'                                  => __DIR__ . '/Applications/Form/FactsFieldset.php',
    'Applications\Form\FilterApplication'                              => __DIR__ . '/Applications/Form/FilterApplication.php',
    'Applications\Form\Mail'                                           => __DIR__ . '/Applications/Form/Mail.php',
    'Applications\Form\SettingsFieldset'                               => __DIR__ . '/Applications/Form/SettingsFieldset.php',
    'Applications\Listener\EventApplicationCreated'                    => __DIR__ . '/Applications/Listener/EventApplicationCreated.php',
    'Applications\Listener\Events\ApplicationEvent'                    => __DIR__ . '/Applications/Listener/Events/ApplicationEvent.php',
    'Applications\Listener\StatusChange'                               => __DIR__ . '/Applications/Listener/StatusChange.php',
    'Applications\Mail\AcceptApplication'                              => __DIR__ . '/Applications/Mail/AcceptApplication.php',
    'Applications\Mail\ApplicationCarbonCopy'                          => __DIR__ . '/Applications/Mail/ApplicationCarbonCopy.php',
    'Applications\Mail\Confirmation'                                   => __DIR__ . '/Applications/Mail/Confirmation.php',
    'Applications\Mail\Forward'                                        => __DIR__ . '/Applications/Mail/Forward.php',
    'Applications\Mail\NewApplication'                                 => __DIR__ . '/Applications/Mail/NewApplication.php',
    'Applications\Mail\StatusChange'                                   => __DIR__ . '/Applications/Mail/StatusChange.php',
    'Applications\Mail\StatusChangeInterface'                          => __DIR__ . '/Applications/Mail/StatusChangeInterface.php',
    'Applications\Options\ModuleOptions'                               => __DIR__ . '/Applications/Options/ModuleOptions.php',
    'Applications\Repository\Application'                              => __DIR__ . '/Applications/Repository/Application.php',
    'Applications\Repository\Event\DeleteRemovedAttachmentsSubscriber' => __DIR__ . '/Applications/Repository/Event/DeleteRemovedAttachmentsSubscriber.php',
    'Applications\Repository\Event\JobReferencesUpdateListener'        => __DIR__ . '/Applications/Repository/Event/JobReferencesUpdateListener.php',
    'Applications\Repository\Event\UpdateFilesPermissionsSubscriber'   => __DIR__ . '/Applications/Repository/Event/UpdateFilesPermissionsSubscriber.php',
    'Applications\Repository\Event\UpdatePermissionsSubscriber'        => __DIR__ . '/Applications/Repository/Event/UpdatePermissionsSubscriber.php',
    'Applications\Repository\Filter\PaginationQuery'                   => __DIR__ . '/Applications/Repository/Filter/PaginationQuery.php',
    'Applications\Repository\Filter\PaginationQueryFactory'            => __DIR__ . '/Applications/Repository/Filter/PaginationQueryFactory.php',
    'Applications\Repository\PaginationList'                           => __DIR__ . '/Applications/Repository/PaginationList.php',
    'Applications\Repository\Subscriber'                               => __DIR__ . '/Applications/Repository/Subscriber.php',
);
