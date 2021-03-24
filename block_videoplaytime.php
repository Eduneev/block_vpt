<?php 

if (file_exists($CFG->dirroot.'/local/vpt/lib.php')) {
	require_once($CFG->dirroot.'/local/vpt/lib.php');	
}


class block_videoplaytime extends block_base {

	public function init() {
        $this->title = get_string('simplehtml', 'block_videoplaytime');
    }

    public function get_content() {
    	global $PAGE;
	    if ($this->content !== null) {
	      return $this->content;
	    }	    

	    $this->content         =  new stdClass;
	    $this->content->text   = $this->available_time();

	    $this->content->footer = '';

	    //$this->content->footer = html_writer::link(new moodle_url('/report/leaderboard/index.php'), 'Leader Board' ) ;

	    return $this->content;

	}

	public function applicable_formats() {
	  return array(
	           'site-index' => false,
	          'course-view' => true, 
	   'course-view-social' => false,
	                  'mod' => true, 	             
	  );
	}

	public function available_time() {
		global $USER, $PAGE;
		$usergroups = current( groups_get_user_groups($PAGE->course->id, $USER->id ) );
		
		$usergroup='';
		foreach ($usergroups as $key => $group) {
			$name = groups_get_group_name($group);
			if (is_vpt_group($name)) {
				$usergroup = $group;
			}
		}
		
		//$usergroup = (isset($usergroups[0])) ? $usergroups[0] : '';
		// print_object($usergroup);exit;

		$data = local_vpt_userAvailableTime($USER->id, $PAGE->course->id);
		$groupData = local_vpt_getGroupDetails($usergroup, $PAGE->course->id, false);
		$html = '';
		if ($data && $groupData) {
			$videoplaytime = convertToHoursMins($data->play_time);//, '%02d hours %02d minutes');
			$availableplaytime = convertToHoursMins($data->available_time);//, '%02d hours %02d minutes');

			// $videoplaytime = gmdate("H:i:s", $data->play_time);
			// $availableplaytime = gmdate("H:i:s", $data->available_time);

			$html = html_writer::start_tag('div', array('class' => 'available-time', 'id' => 'available-time' ) );
			if (!$data->maximum_time) {
				$maximumplaytime = convertToHoursMins($groupData->maximum_time);				
			} else {
				$maximumplaytime = convertToHoursMins($data->maximum_time);				

			}
			$html .= '<label>'.get_string('allocation', 'block_videoplaytime').'</label>';
			$html .= '<span> <b> '.$maximumplaytime.' </b>  </span> <br />';

			// }
			$html .= '<label>'.get_string("utilised", 'block_videoplaytime').' :</label>';
			$html .= '<span> <b> '.$videoplaytime.' </b> </span> <br />';

			$html .= '<label>'.get_string("available", 'block_videoplaytime').' :</label>';
			$html .= '<span> <b> '.$availableplaytime.' </b>  </span> <br />';


			$html .= html_writer::end_tag('div');
		} else if ($groupData) {
			$maximumplaytime = convertToHoursMins($groupData->maximum_time);
			$html .= html_writer::start_tag('div', array('class' => 'maximum-time', 'id' => 'maximum-time'));
			$html .= '<label>'.get_string('allocation', 'block_videoplaytime').'</label>';
			$html .= '<span> <b> '.$maximumplaytime.' </b>  </span> <br />';
			$html .= '</div>';
		} else {

			$html .= html_writer::start_tag('div', array('class' => 'no-max-time', 'id' => 'no-maximum-time'));
			$html .= '<label>'.get_string('noallocation', 'block_videoplaytime').'</label>';
			
			$html .= '</div>';
		}

		return $html;
	}
}