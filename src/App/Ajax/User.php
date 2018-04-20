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
class User
{

    /**
     * @param Request $request
     * @return \Tk\Response
     */
    public function doFindFiltered(Request $request)
    {
        $status = 200;  // change this on error
        
        $users = array();
        $filter = $request->all();
        unset($filter['subjectId']);
        
        if (!empty($filter['keywords'])) {
            if ($filter['keywords'][0] == '*') {    // Keep wildcard char as an undocumented feature for now
                $filter['keywords'] = '';
            }
            $users = \App\Db\UserMap::create()->findFiltered($filter, \Tk\Db\Tool::create('a.name', 25))->toArray();
            foreach ($users as $user) {
                $user->id = '';
                $user->username = '';
                $user->password = '';
            }
        }
        return \Tk\ResponseJson::createJson($users, $status);
    }

}