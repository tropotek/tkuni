<?php
namespace App;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Uri extends \Tk\Uri
{

    /**
     * A static factory method to facilitate inline calls
     *
     * <code>
     *   \Tk\Uri::create('http://example.com/test');
     * </code>
     *
     * @param null|string|\Tk\Uri $spec
     * @return string|\Tk\Uri|static
     */
    public static function createHomeUrl($spec = null)
    {
        if ($spec instanceof \Tk\Uri)
            return clone $spec;

        $home = '';
        $user = \App\Config::getInstance()->getUser();
        if ($user instanceof \App\Db\User) {
            $home = $user->getHomeUrl();
            if($home instanceof \Tk\Uri) {
                $home = $home->getRelativePath();
            }
            $home = dirname($home);
        }
        return new static($home . '/' . trim($spec,'/'));
    }

    /**
     * Create a course URL in the form of '/ems/staff/VETS50001_2014_SM1/index.html'
     *
     * @param null|string|\Tk\Uri $spec
     * @param null|\App\Db\Course $course
     * @return string|\Tk\Uri|static
     */
    public static function createCourseUrl($spec = null, $course = null)
    {
        if ($spec instanceof \Tk\Uri)
            return clone $spec;

        if ($course === null)
            $course = \App\Config::getInstance()->getCourse();
        $courseCode = '';
        if ($course) {
            $courseCode = $course->code . '/';
        }
        return self::createHomeUrl($courseCode . trim($spec,'/'));
    }

    /**
     * Create a course URL in the form of '/ems/staff/VETS50001_2014_SM1/index.html'
     *
     * @param null|string|\Tk\Uri $spec
     * @param null|\App\Db\Institution $institution
     * @return string|\Tk\Uri|static
     */
    public static function createInstitutionUrl($spec = null, $institution = null)
    {
        if ($spec instanceof \Tk\Uri)
            return clone $spec;
        if ($institution === null)
            $institution = \App\Config::getInstance()->getInstitution();
        return self::create('/inst/'.$institution->hash . '/' . trim($spec,'/'));
    }

    /**
     * Call this to ensure the breadcrumb system ignores this URL
     *
     * @param bool $b
     * @return static
     */
    public function ignoreCrumb($b = true)
    {
        if ($b)
            $this->set(\App\Ui\Crumbs::CRUMB_IGNORE);
        else
            $this->remove(\App\Ui\Crumbs::CRUMB_IGNORE);
        return $this;
    }

    /**
     * Debug Only
     * Call this to enable/disable log entries for this url
     *
     * @param bool $b
     * @return static
     */
    public function noLog($b = true)
    {
        if (!\App\Config::getInstance()->isDebug()) return $this;

        if ($b)
            $this->set('nolog');
        else
            $this->remove('nolog');
        return $this;
    }

}