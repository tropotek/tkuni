<?php
namespace App\Ui;

use Dom\Template;
use Tk\Request;

/**
 * This class uses the bootstrap dialog box model
 * @link http://getbootstrap.com/javascript/#modals
 *
 *
 * <code>
 * // doDefault()
 * $this->dialog = new \App\Ui\EnrollmentDialog('Enroll Student');
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
class EnrollmentDialog extends DialogBox
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
     * Process the enrollments as submitted from the dialog
     *
     * @param Request $request
     * @throws \Tk\Exception
     */
    public function execute(Request $request)
    {
        if (!$request->has('enroll')) {
            return;
        }
        $this->course = \App\Db\CourseMap::create()->find($request->get('courseId'));
        if (!$this->course)
            throw new \Tk\Exception('Invalid course details');

        $list = array();

        // Check file list
        if ($request->getUploadedFile('csvFile') && $request->getUploadedFile('csvFile')->getError() == \UPLOAD_ERR_OK) {
            /** @var \Tk\UploadedFile $file */
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
        } else if ($request->get('email')) {
            $list[] = array(
                'email' => $request->get('email'),
                'uid' => $request->get('uid')
            );
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

            if (!\App\Db\CourseMap::create()->hasEnrollment($this->course->id, $email)) {
                \App\Db\CourseMap::create()->enrollUser($this->course->id, $email, $uid);
                $user = \App\Db\UserMap::create()->findByEmail($email, $this->course->institutionId);
                if ($user) {
                    \App\Db\CourseMap::create()->addUser($this->course->id, $user->id);
                }

                $success[] = $i . ' - Added ' . $email . ' to the course enrollment list';
            } else {
                $info[] = $i . ' - User ' . $email . ' already enrolled, nothing done.';
            }
        }
        if (count($info)) {
            \App\Alert::addInfo(count($info) . ' records already enrolled and ignored.');
        }
        if (count($success)) {
            \App\Alert::addSuccess(count($success) . ' records successfully added to the enrolment list.');
        }
        if (count($error)) {
            \App\Alert::addError(count($error) . ' records contained errors.');
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
        $url = \App\Factory::getRequest()->getUri()->toString();
        $html = <<<HTML
<form id="addEnromentForm" method="POST" action="$url" enctype="multipart/form-data">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#add" aria-controls="add" role="tab" data-toggle="tab">Add User</a></li>
    <li role="presentation"><a href="#files" aria-controls="files" role="tab" data-toggle="tab">Upload CSV List</a></li>
    <li role="presentation"><a href="#list" aria-controls="list" role="tab" data-toggle="tab">Paste CSV List</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="add">
      <div class="form-group form-group-sm">
        <label for="fid-email" class="control-label">* Email:</label>
        <input type="email" class="form-control" id="fid-email" name="email" />
      </div>
      <!-- div class="form-group form-group-sm">
        <label for="fid-uid" class="control-label">Student Number:</label>
        <input type="text" class="form-control" id="fid-uid" name="uid" />
      </div -->
    </div>
    <div role="tabpanel" class="tab-pane" id="files">
      <div class="form-group form-group-sm">
        <label for="fid-csvFile" class="control-label">* Csv File:</label>
        <div>
        <input type="file" class="form-control" id="fid-csvFile" name="csvFile"/>
        </div>
      </div>
      <div class=""><p>The CSV file should contain the users email address per line</p></div>
    </div>
    <div role="tabpanel" class="tab-pane" id="list">
      <div class="form-group form-group-sm">
        <label for="fid-csvList" class="control-label">* CSV List:</label>
        <textarea class="form-control" id="fid-csvList" name="csvList" style="height: 90px;"></textarea>
      </div>
      <div class=""><p>The CSV List should contain the users email address per line</p></div>
    </div>
    
  </div>
    
</form>
HTML;
        $this->setBody($html);


        $js = <<<JS
jQuery(function($) {
  $('#fid-Enroll').on('click', function(e) {
    var form = $('#addEnromentForm');
    $('<input type="submit" name="enroll" value="Enroll" />').hide().appendTo(form).click().remove();
  });
});
JS;
        $template->appendJs($js);




        return parent::show();
    }

}
