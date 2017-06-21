<?php
namespace App\Ui\Dialog;

use Tk\Request;

/**
 * This class uses the bootstrap dialog box model
 * @link http://getbootstrap.com/javascript/#modals
 *
 *
 * <code>
 * // doDefault()
 * $this->dialog = new \App\Ui\Dialog\PreEnrollment('Enroll Student');
 * $this->dialog->execute($request);
 *
 * ...
 * // show()
 * $template->insertTemplate('dialog', $this->dialog->show());
 * $template->setAttr('modelBtn', 'data-target', '#'.$this->dialog->getId());
 *
 * </code>
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class PreEnrollment extends Iface
{

    /**
     * @var \App\Db\Course
     */
    protected $course = null;

    /**
     * DialogBox constructor.
     * @param $title
     */
    public function __construct($title)
    {
        parent::__construct($title);
        $this->addButton('Close');
        $this->addButton('Enroll', array('class' => 'btn btn-primary'));
    }

    /**
     * @return string
     */
    public function getEnrollButtonId()
    {
        return $this->getId().'-Enroll';
    }

    /**
     * Process the enrolments as submitted from the dialog
     *
     * @param Request $request
     * @throws \Tk\Exception
     */
    public function execute(Request $request)
    {
        if (!$request->has('enroll')) {
            return;
        }
        //$this->course = \App\Factory::getCourse();
        $this->course = \App\Db\CourseMap::create()->find($request->get('courseId'));
        if (!$this->course)
            throw new \Tk\Exception('Invalid course details');

        $list = array();

        // Check file list
        if ($request->getUploadedFile('csvFile') && $request->getUploadedFile('csvFile')->getError() == \UPLOAD_ERR_OK) {
            /* @var \Tk\UploadedFile $file */
            $file = $request->getUploadedFile('csvFile');
            if (($handle = fopen($file->getFile(), 'r')) !== FALSE) {
                $list = $this->processCsv($handle);
            }
        } else if($request->get('csvList')) {
            // Check textarea list
            $csvList = $request->get('csvList');
            if (($handle = fopen('data://text/plain,'.$csvList, 'r')) !== FALSE) {
                $list = $this->processCsv($handle);
            }
        }
        
        $error = array();
        $success = array();
        $info = array();
        foreach ($list as $i => $arr) {
            $email = trim(strip_tags($arr['email']));
            $uid = '';
            if (isset($arr['uid']))
                $uid = trim(strip_tags($arr['uid']));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error[$email] = $i . ' - Cannot locate student enrollment email';
                $request->getUri()->redirect();
            }

            // Add users if found
            if (!\App\Db\CourseMap::create()->hasPreEnrollment($this->course->getId(), $email)) {
                \App\Db\CourseMap::create()->addPreEnrollment($this->course->getId(), $email, $uid);
                $user = \App\Db\UserMap::create()->findByEmail($email, $this->course->institutionId);
                if ($user) {
                    \App\Db\CourseMap::create()->addUser($this->course->getId(), $user->getId());
                }

                $success[] = $i . ' - Added ' . $email . ' to the course enrollment list';
            } else {
                $info[] = $i . ' - User ' . $email . ' already enrolled, nothing done.';
            }
        }
        if (count($info)) {
            \Tk\Alert::addInfo(count($info) . ' records already enrolled and ignored.');
        }
        if (count($success)) {
            \Tk\Alert::addSuccess(count($success) . ' records successfully added to the enrollment list.');
        }
        if (count($error)) {
            \Tk\Alert::addError(count($error) . ' records contained errors.');
        }

        $request->getUri()->redirect();
    }


    /**
     * @param $stream
     * @return array
     */
    private function processCsv($stream)
    {
        $list = array();
        $row = 1;

        while (($data = fgetcsv($stream, 1000, ',')) !== FALSE) {
            $num = count($data);
            $list[$row] = array();
            for ($c=0; $c < $num; $c++) {
                switch($c) {
                    case 0:
                        $list[$row]['email'] = $data[$c];
                        break;
                    case 1:
                        $list[$row]['uid'] = $data[$c];
                        break;
                }
            }
            $row++;
        }
        fclose($stream);
        return $list;
    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = $this->getTemplate();
        $this->setBody($this->makeBodyHtml());
        $dialogId = $this->getId();
        $buttonId = $this->getEnrollButtonId();
        
        $js = <<<JS
jQuery(function($) {
  var dialog = $('#$dialogId');
  
  $('#$buttonId').on('click', function(e) {
    var form = $('#addEnrollmentForm');
    $('<input type="submit" name="enroll" value="Enroll" />').hide().appendTo(form).click().remove();
  });
});
JS;
        $template->appendJs($js);
        
        return parent::show();
    }

    /**
     * DomTemplate magic method
     *
     * @return string
     */
    public function makeBodyHtml()
    {
        $url = \App\Factory::getRequest()->getUri()->toString();
        $xhtml = <<<HTML
<form id="addEnrollmentForm" method="POST" action="$url" enctype="multipart/form-data">

  <div class="form-group form-group-sm">
    <label for="fid-csvFile" class="control-label">* Csv File:</label>
    <div>
    <input type="file" class="form-control tk-fileinput" id="fid-csvFile" name="csvFile"/>
    </div>
  </div>
  <p>OR</p>
  <div class="form-group form-group-sm">
    <label for="fid-csvList" class="control-label">* CSV List:</label>
    <textarea class="form-control" id="fid-csvList" name="csvList" style="height: 90px;"></textarea>
  </div>

  <p>NOTE: The CSV List should contain one email address per line</p>
    
</form>
HTML;
        return $xhtml;
    }
}
