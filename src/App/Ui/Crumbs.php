<?php
namespace App\Ui;

use Tk\Request;

/**
 * Use this object to track and render a crumb stack
 *
 * See the controlling object \App\Listeners\CrumbsHandler to
 * view its implementation.
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Crumbs extends \Dom\Renderer\Renderer
{

    const CRUMB_RESET = 'crumb_reset';
    const CRUMB_IGNORE = 'crumb_ignore';

    /**
     * @var array
     */
    protected $list = array();



    /**
     * Crumbs constructor.
     */
    private function __construct() { }

    /**
     *
     * @return Crumbs
     */
    static public function create()
    {
        $obj = new static();
        return $obj;
    }

    /**
     * Use to restore crumb list.
     * format:
     *   array(
     *     'Page Name' => '/page/url/pageUrl.html'
     *   );
     *
     * @return Crumbs
     */
    static public function createFromArray($list)
    {
        $obj = new static();
        $obj->list = $list;
        return $obj;
    }


    /**
     * Get teh crumb list
     *
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param $list
     */
    public function setList($list = array())
    {
        $this->list = $list;
    }

    /**
     * @return \Tk\Uri
     */
    public function getBackUrl()
    {
        $url = '';
        if (count($this->list) == 1) {
             $url = end($this->list);
        } if (count($this->list) > 1) {
            end($this->list);
            $url = prev($this->list);
        }
        return \Tk\Uri::create($url);
    }

    /**
     * @param string $title
     * @param \Tk\Uri|string $url
     * @return $this
     */
    public function addCrumb($title, $url)
    {
        $url = \Tk\Uri::create($url);
        $this->list[$title] = $url->toString();
        return $this;
    }

    /**
     * @param string $title
     * @param \Tk\Uri|string $url
     * @return $this
     */
    public function replaceCrumb($title, $url) {
        array_pop($this->list);
        return $this->addCrumb($title, $url);
    }

    /**
     * @param $title
     * @return array
     */
    public function trimByTitle($title) {
        $l = array();
        foreach ($this->list as $t => $u) {
            if ($title == $t) break;
            $l[$t] = $u;
        }
        $this->list = $l;
        return $l;
    }

    /**
     * @param $url
     * @param bool $ignoreQuery
     * @return array
     */
    public function trimByUrl($url, $ignoreQuery = true) {
        $url = \Tk\Uri::create($url);
        $l = array();
        foreach ($this->list as $t => $u) {
            if ($ignoreQuery) {
                if (\Tk\Uri::create($u)->getRelativePath() == $url->getRelativePath()) {
                    break;
                }
            } else {
                if (\Tk\Uri::create($u)->toString() == $url->toString()) {
                    break;
                }
            }
            $l[$t] = $u;
        }
        $this->list = $l;
        return $l;
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = $this->getTemplate();

        $i = 0;
        foreach ($this->list as $title => $url) {
            $repeat = $template->getRepeat('li');
            if (!$repeat) continue;         // ?? why and how does the repeat end up null.
            if ($i < count($this->list)-1) {
                $repeat->setAttr('url', 'href', \Tk\Uri::create($url)->toString());
                $repeat->insertText('url', $title);
            } else {    // Last item
                $repeat->insertText('li', $title);
                $repeat->addCss('li','active');
            }

            $repeat->appendRepeat();
            $i++;
        }

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<ol class="breadcrumb" var="breadcrumb">
  <li repeat="li" var="li"><a href="#" var="url"></a></li>
</ol>
HTML;

        return \Dom\Loader::load($html);
    }


}
