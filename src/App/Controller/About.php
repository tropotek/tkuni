<?php
namespace App\Controller;

use Tk\Request;
use Tk\Form;

/**
 * Class Contact
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class About extends Iface
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
        parent::__construct('About Us');
    }

    /**
     * doDefault
     *
     * @param Request $request
     * @return \App\Page\PublicPage
     */
    public function doDefault(Request $request)
    {



        return $this->show();
    }

    /**
     * show()
     *
     * @return \App\Page\PublicPage
     */
    public function show()
    {
        $template = $this->getTemplate();


        return $this->getPage()->setPageContent($template);
    }



    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {

        $xhtml = <<<HTML
<div class="">
 <p>About Us Content.</p>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}