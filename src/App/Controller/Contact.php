<?php
namespace App\Controller;

use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;

/**
 * Class Contact
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Contact extends Iface
{

    /**
     * @var Form
     */
    protected $form = null;

    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Contact Us');
    }

    /**
     * doDefault
     *
     * @param Request $request
     * @return \App\Page\PublicPage
     */
    public function doDefault(Request $request)
    {
        $this->config = \Tk\Config::getInstance();

        $this->form = new Form('contactForm');
        
        $this->form->addField(new Field\Input('name'));
        $this->form->addField(new Field\Input('email'));
        
        $opts = new Field\Option\ArrayIterator(array('General', 'Services', 'Orders'));
        $this->form->addField(new Field\Select('type[]', $opts));
        
        $this->form->addField(new Field\File('attach[]', $request));
        $this->form->addField(new Field\Textarea('message'));
        
        $this->form->addField(new Event\Button('send', array($this, 'doSubmit')));
        
        // Find and Fire submit event
        $this->form->execute();

        return $this->show();
    }

    /**
     * show()
     *
     * @return mixed
     */
    public function show()
    {
        $template = $this->getTemplate();

        // Render the form
        $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
        $ren->show();

        return $this->getPage()->setPageContent($template);
    }

    /**
     * doSubmit()
     *
     * @param Form $form
     */
    public function doSubmit($form)
    {
        $values = $form->getValues();

        /** @var Field\File $attach */
        $attach = $form->getField('attach');

        if (empty($values['name'])) {
            $form->addFieldError('name', 'Please enter your name');
        }
        if (empty($values['email']) || !filter_var($values['email'], \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('email', 'Please enter a valid email address');
        }
        if (empty($values['message'])) {
            $form->addFieldError('message', 'Please enter some message text');
        }

        // validate any files
        $attach->isValid();

        if ($this->form->hasErrors()) {
            return;
        }
        if ($attach->hasFile()) {
            //$attach->moveTo($this->getConfig()->getDataPath() . '/contact/' . date('d-m-Y') . '-' . str_replace('@', '_', $values['email']));
        }

        if ($this->sendEmail($form)) {
            \Tk\Alert::addSuccess('<strong>Success!</strong> Your form has been sent.');
        }

        \Tk\Uri::create()->redirect();
    }


    /**
     * sendEmail()
     *
     * @param Form $form
     * @return bool
     */
    private function sendEmail($form)
    {
        $name = $form->getFieldValue('name');
        $email = $form->getFieldValue('email');
        $type = '';
        if (is_array($form->getFieldValue('type')))
            $type = implode(', ', $form->getFieldValue('type'));
        $msg = nl2br($form->getFieldValue('message'));
        $attachCount = '';
        /** @var Field\File $field */
        $field = $form->getField('attach');
        if ($field->hasFile()) {
            $attachCount = 'Attachments: ' . $field->getUploadedFile()->getFilename();
        }

        $body = <<<MSG
<div>
<p>
<b>Name:</b> $name<br/>
<b>Email:</b> $email<br/>
<b>Type:</b> $type<br/>
</p>
<p>
<b>Message:</b><br/>
  $msg
</p>
<p>
$attachCount
</p>
MSG;
        

        $message = new \Tk\Mail\Message(\App\Factory::createMailTemplate($body), $this->getConfig()->get('site.name') . ':'. $name .' Contact Form Submission', $this->getConfig()->get('site.email'), $email);

        if ($field->hasFile()) {
            $message->addAttachment($field->getUploadedFile()->getFile(), $field->getUploadedFile()->getFilename());
        }
        \App\Factory::getEmailGateway()->send($message);

        return true;
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        return \Dom\Loader::loadFile($this->getPage()->getTemplatePath().'/xtpl/public/contact.xtpl');
    }
}