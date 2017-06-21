<?php
namespace App\Ajax;

use Tk\Request;

/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Course
{

    /**
     * @param Request $request
     * @return \Tk\Response
     */
    public function doFindFiltered(Request $request)
    {
        $status = 200;  // change this on error
        $filter = $request->all();
        if (!empty($filter['courseId'])) {
            $filter['exclude'] = $filter['courseId'];
            unset($filter['courseId']);
        }
        if (empty($filter['keywords'])) {
            unset($filter['keywords']);
        }
        if (!empty($filter['ignoreUser']) && !empty($filter['userId'])) {
            unset($filter['userId']);
        }

        $list = \App\Db\CourseMap::create()->findFiltered($filter);
        $data = array();
        
        foreach ($list as $course) {
            $data[] = $course;
        }
        return \Tk\ResponseJson::createJson($data, $status);
    }

}