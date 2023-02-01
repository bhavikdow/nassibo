<?php 
defined("BASEPATH") or exit ("No direct script access allowed");

/**
 * 
 */
class Api_model extends CI_Model{
	
	public function __construct(){
		parent::__construct();
	}

    public function getUserData($token){
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function getReligionSpecification($token){
        $this->db->select('belief_specifications.name as religion_specification');
        $this->db->from('users');
        $this->db->join('belief_specifications', 'belief_specifications.id = users.specify_religion');
        $this->db->where('belief_specifications.status', 'Active');
        $this->db->where('users.token',  $token);
        $result = $this->db->get();
        return $result->row();
    }

    public function getUserHabits($user_id){
        $this->db->select('user_habits.habits as id, habits.name, habits.image_url');
        $this->db->from('user_habits');
        $this->db->join('habits', 'habits.id = user_habits.habits');
        $this->db->where('user_habits.user_id', $user_id);
        $this->db->where('habits.status', 'Active');
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getUserHobbiesId($user_id){
        $this->db->select('user_hobbies.hobbies as hobbies_id');
        $this->db->from('user_hobbies');
        $this->db->join('hobbies', 'hobbies.id = user_hobbies.hobbies');
        $this->db->where('user_hobbies.user_id', $user_id);
        $this->db->where('hobbies.status', 'Active');
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getUserHobbiesName($user_id){
        $this->db->select('hobbies.name as hobbies_name');
        $this->db->from('user_hobbies');
        $this->db->join('hobbies', 'hobbies.id = user_hobbies.hobbies');
        $this->db->where('user_hobbies.user_id', $user_id);
        $this->db->where('hobbies.status', 'Active');
        $result = $this->db->get();
        return $result->result_array();
    }    

    public function getUserHobbies($user_id){
        $this->db->select('user_hobbies.hobbies as id, hobbies.name');
        $this->db->from('user_hobbies');
        $this->db->join('hobbies', 'hobbies.id = user_hobbies.hobbies');
        $this->db->where('user_hobbies.user_id', $user_id);
        $this->db->where('hobbies.status', 'Active');
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getUserImages($user_id){
        $this->db->select('user_images.id, user_images.image_url');
        $this->db->from('user_images');
        $this->db->where('user_images.user_id', $user_id);
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getUserLanguages($user_id){
        $this->db->select('user_languages.language_id as id, user_languages.language_name');
        $this->db->from('user_languages');
        $this->db->where('user_languages.user_id', $user_id);
        $result = $this->db->get();
        return $result->result_array();
    }

	public function checkUserData($signup_type, $country_code, $signup_var){
		if($signup_type == 'email'){
			$result = $this->db->get_where('users', ['email' => $signup_var, 'status !=' => 'Deleted']);
		}else{
			$result = $this->db->get_where('users', ['country_code' => $country_code, 'phone' => $signup_var, 'status !=' => 'Deleted']);
		}
		return $result->row_array();
	}

    public function checkSocialUserData($token){
        $result = $this->db->get_where('users', ['token' => $token, 'status !=' => 'Deleted']);
        return $result->row_array();
    }

	public function updateOtp($signup_type, $otp, $country_code, $signup_var, $token){
		$data = array(
			'otp' => hash('sha256', $otp),
            'status' => 'Active',
			'token' => $token
		);
		if($signup_type == 'email'){
			$this->db->update('users', $data, ['email' => $signup_var]);
		}else{
			$this->db->update('users', $data, ['country_code' => $country_code, 'phone' => $signup_var]);
		}
		return $this->db->affected_rows();
	}

	public function insertOtp($signup_type, $otp, $country_code, $country_flag, $signup_var){
		if($signup_type == 'email'){
			$data = array('otp' => hash('sha256', $otp), 'email' => $signup_var);
		}else{
			$data = array('otp' => hash('sha256', $otp), 'country_code' => $country_code, 'country_flag' => $country_flag, 'phone' => $signup_var);
		}
		$this->db->insert('user_otp', $data);
		return $this->db->insert_id();
	}

    public function getUserOTPData($signup_type, $country_code, $signup_var){
        $this->db->select('*');
        $this->db->from('user_otp');
        if($signup_type == 'email'){
            $this->db->where('email', $signup_var);
        }else{
            $this->db->where('country_code', $country_code, 'phone', $signup_var);
        }
        $result = $this->db->get();
        return $result->row_array();
    }

    public function insertSocialUserData($social_type, $email, $token, $social_id){
        $data = array(
            'email' => $email,
            'token' => $token,
            'source' => $social_type,
            'social_id' => $social_id,
        );
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function updateSocialUserData($social_type, $email, $token, $social_id){
        $data = array(
            'token' => $token,
            'source' => $social_type,
            'social_id' => $social_id,
            'status' => 'Active',
        );
        $this->db->update('users', $data, ['email' => $email]);
        return $this->db->affected_rows();
    }

    public function updateFBToken($token, $firebase_token){
        $data = array(
            'firebase_token' => $firebase_token
        );
        $this->db->update('users', $data, ['token' => $token]);
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function updateTokenAndFBToken($token){
        $data = array(
            'token' => NULL,
            'firebase_token' => NULL,
            'online_status' => 'Offline'
        );
        $this->db->update('users', $data, ['token' => $token]);
        return true;
    }

    public function updateOnlineStatus($token, $status){
        if($status == 'true'){
            $online_status = 'Online';
        }else{
            $online_status = 'Offline';
        }
        $data = array(
            'online_status' => $online_status
        );
        $this->db->update('users', $data, ['token' => $token]);
        return true;
    }

	public function verifyOtp($signup_type, $country_code, $signup_var, $otp){
		$this->db->select('*');
        $this->db->from('users');
        $this->db->where('otp', hash('sha256', $otp));
		if($signup_type == 'email'){
			$this->db->where('email', $signup_var);
		}else{
			$this->db->where(['country_code' => $country_code, 'phone' => $signup_var]);
		}
        $query = $this->db->get();
        return $query->row_array();
	}

    public function updateOtpField($signup_type, $country_code, $country_flag, $signup_var){
        $data = array(
            'otp' => NULL,
            'online_status' => 'Online'
        );
        if($signup_type == 'email'){
            $this->db->update('users', $data, ['email' => $signup_var]);
            $result = $this->db->get_where('users', ['email' => $signup_var]);
        }else{
            $this->db->update('users', $data, ['country_code' => $country_code, 'country_flag' => $country_flag, 'phone' => $signup_var]);
            $result = $this->db->get_where('users', ['country_code' => $country_code, 'phone' => $signup_var]);
        }
        return $result->row_array();
    }

    public function insertOtpField($signup_type, $country_code, $country_flag, $signup_var, $token){
        if($signup_type == 'email'){
            $data = array('email' => $signup_var, 'token' => $token);
        }else{
            $data = array('country_code' => $country_code, 'country_flag' => $country_flag, 'phone' => $signup_var, 'token' => $token);
        }
        $this->db->insert('users', $data);
        $id = $this->db->insert_id();
        $result = $this->db->get_where('users', ['id' => $id]);
        return $result->row_array();
    }

    public function verifyNewUserOtp($signup_type, $country_code, $signup_var, $otp){
        $this->db->select('*');
        $this->db->from('user_otp');
        $this->db->where('otp', hash('sha256', $otp));
        if($signup_type == 'email'){
            $this->db->where('email', $signup_var);
        }else{
            $this->db->where('country_code', $country_code, 'phone', $signup_var);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    public function verifyUserOtp($country_code, $phone, $otp, $token){
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('otp', hash('sha256', $otp));
        $this->db->where(['country_code' => $country_code, 'phone' => $phone]);
        $this->db->where('token', $token);
        $query = $this->db->get();
        return $query->row_array();
    }

	public function getPages($page){
        $query = $this->db->get_where('setting', ['type' => $page]);
        return $query->row_array();
    }

    public function getUserIdByToken($token){
        $this->db->select('id, status, step, interested_in, latitude, longitude, age_range, age_range_status, distance_range, distance_unit, global, blocked, app_language');
        $query = $this->db->get_where('users', ['token' => $token]);
        return $query->row_array();
    }

    public function getUserById($id){
        $this->db->select('image_url');
        $query = $this->db->get_where('users', ['id' => $id]);
        return $query->row_array();
    }

    public function updateUserName($token){
    	$data = array(
    		'first_name' => $this->input->post('first_name'),
    		'last_name' => $this->input->post('last_name'),
    		'step' => 'age_gender'
    	);
        if(empty($data['last_name'])){
            unset($data['last_name']);
        }
        if(empty($data['first_name'])){
            unset($data['first_name']);
        }
    	$this->db->update('users', $data, ['token' => $token]);
    	$result = $this->db->get_where('users', ['token' => $token]);
    	return $result->row_array();
    }

    public function updateGenderAge($token){
    	$data = array(
    		'gender' => $this->input->post('gender'),
    		'show_gender' => $this->input->post('show_gender'),
    		'age' => $this->input->post('age'),
    		'step' => 'belief'
    	);
        if(empty($data['gender'])){
            unset($data['gender']);
        }
        if(empty($data['show_gender'])){
            unset($data['show_gender']);
        }
        if(empty($data['age'])){
            unset($data['age']);
        }
    	$this->db->update('users', $data, ['token' => $token]);
    	$result = $this->db->get_where('users', ['token' => $token]);
    	return $result->row_array();
    }

    public function deleteHabits($user_id){
        $this->db->delete('user_habits', ['user_id' => $user_id]);
        return $this->db->affected_rows();
    }

    public function removeLanguages($user_id){
        $this->db->delete('user_languages', ['user_id' => $user_id]);
        return $this->db->affected_rows();
    }

    public function insertLanguages($user_id, $language_id, $language_name){
        $data = array(
            'user_id' => $user_id,
            'language_id' => $language_id,
            'language_name' => $language_name
        );
        $this->db->insert('user_languages', $data);
        return $this->db->insert_id();
    }

    public function insertHabits($user_id, $habits){
        foreach ($habits as $key => $value) {
            $data = array(
                'user_id' => $user_id,
                'habits' => $value
            );
            $this->db->insert('user_habits', $data);
        }
        return $this->db->insert_id();
    }

    public function deleteHobbies($user_id){
        $this->db->delete('user_hobbies', ['user_id' => $user_id]);
        return $this->db->affected_rows();
    }

    public function insertHobbies($user_id, $hobbies){
        foreach ($hobbies as $key => $value) {
            $this->db->select('hobbies.name');
            $this->db->where('id', $value);
            $result = $this->db->get('hobbies')->row_array();
            $data = array(
                'user_id' => $user_id,
                'hobbies' => $value,
                'hobbies_name' => $result['name']
            );
            $this->db->insert('user_hobbies', $data);
        }
        return $this->db->insert_id();
    }

    public function updateBelief($belief, $belief_specification, $token){
        $data = array(
            'religion' => $belief,
            'specify_religion' => $this->input->post('belief_specification'),
            'step' => 'user_images'
        );
        $this->db->update('users', $data, ['token' => $token]);
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

	public function getAllLanguages(){
        $this->db->select('languages.id, languages.name as language_name, languages.language_code');
		$result = $this->db->get('languages');
		return $result->result_array();
	}

    public function getAllHabits(){
        $this->db->select('habits.id, habits.name, habits.image_url');
        $this->db->order_by('id', 'asc');
        $this->db->where('status', 'Active');
        $result = $this->db->get('habits');
        return $result->result_array();
    }

    public function getAllHobbies(){
        $this->db->select('hobbies.id, hobbies.name');
        $this->db->order_by('id', 'desc');
        // $this->db->where('user_id', $user_id);
        $this->db->where('user_id', NULL);
        $this->db->where('status', 'Active');
        $result = $this->db->get('hobbies');
        return $result->result_array();
    }

    public function getAllUserHobbies($user_id){
        $this->db->select('hobbies.id, hobbies.name');
        $this->db->order_by('id', 'desc');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'Active');
        $result = $this->db->get('hobbies');
        return $result->result_array();
    }

    public function getAllBeliefSpecification(){
        $this->db->select('belief_specifications.id, belief_specifications.name');
        $this->db->order_by('id', 'asc');
        $this->db->where('status', 'Active');
        $result = $this->db->get('belief_specifications');
        return $result->result_array();
    }

    public function getAllHouseRules(){
        $this->db->select('house_rules.id, house_rules.title, house_rules.text');
        $this->db->where('status', 'Active');
        $result = $this->db->get('house_rules');
        return $result->result_array();
    }

    public function getAllReligion(){
        $this->db->select('religion.id, religion.name, religion.image_url');
        $this->db->where('status', 'Active');
        $result = $this->db->get('religion');
        return $result->result_array();
    }

    public function getReportReasons(){
        $this->db->select('report_options.id, report_options.report_options');
        $this->db->where('status', 'Active');
        $this->db->order_by('id', 'desc');
        $result = $this->db->get('report_options');
        return $result->result_array();
    }

	public function getLanguagesName($languages){
		$this->db->select('*');
		$this->db->from('languages');
		$this->db->where_in('id', $languages);
		$result = $this->db->get();
		return $result->result_array();
	}

    public function getLanguageNameByCode($language_code){
        $this->db->select('*');
        $this->db->from('languages');
        $this->db->where('language_code', $language_code);
        $result = $this->db->get();
        return $result->row_array();
    }

	public function updateUserProfileImage($image_url, $token){
    	$data = array(
    		'image_url' => $image_url,
    		'step' => 'interested_in'
    	);
    	$this->db->update('users', $data, ['token' => $token]);
    	$result = $this->db->get_where('users', ['token' => $token]);
    	return $result->row_array();
    }

    public function updateUserImage($image_url, $token){
        $data = array(
            'image_url' => $image_url,
        );
        $this->db->update('users', $data, ['token' => $token]);
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function deleteUserImages($image_id, $user_id){
        if(!empty($image_id)){
            $this->db->delete('user_images', ['id' => $image_id]);
        }else{
            $this->db->delete('user_images', ['user_id' => $user_id]);
        }
        return $this->db->affected_rows();
    }

    public function insertUserImages($image_url, $id){
    	$data = array(
    		'user_id' => $id,
    		'image_url' => $image_url
    	);
    	$this->db->insert('user_images', $data);
    	return $this->db->insert_id();
    }

    public function updateInterestedIn($token){
    	$data = array(
    		'interested_in' => $this->input->post('interested_in'),
    		'step' => 'hobbies'
    	);
    	$this->db->update('users', $data, ['token' => $token]);
    	$result = $this->db->get_where('users', ['token' => $token]);
    	return $result->row_array();
    }

    public function updateUserStep($token){
        $this->db->update('users', ['step' => 'about_me'], ['token' => $token]);
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function updateDescription($token){
        if(!empty($this->input->post('description'))){
            $desc = $this->input->post('description');
        }else{
            $desc = NULL;
        }
    	$data = array(
    		'about_me' => $desc,
            'step' => 'completed'
    	);
    	$this->db->update('users', $data, ['token' => $token]);
    	$result = $this->db->get_where('users', ['token' => $token]);
    	return $result->row_array();
    }

    public function updateUserOtp($otp, $token){
        $data = array(
            'otp' => hash('sha256', $otp)
        );
        $this->db->update('users', $data, ['token' => $token]);
        $this->db->select('users.id, users.otp');
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function updatePhoneNumber($country_code, $country_flag, $phone, $token){
        $data = array(
            'country_code' => $country_code,
            'country_flag' => $country_flag,
            'phone' => $phone,
            'otp' => NULL
        );
        $this->db->update('users', $data, ['token' => $token]);
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function checkPhoneNumber($country_code, $phone, $token){
        $this->db->select('users.id');
        $this->db->from('users');
        $this->db->where(['country_code' => $country_code, 'phone' => $phone, 'token!=' => $token, 'status!= ' => 'Deleted']);
        $result = $this->db->get();
        return $result->row_array();
    }

    public function deleteUserAccount($token){
        $this->db->update('users', ['status' => 'Deleted'], ['token' => $token]);
        return $this->db->affected_rows();
    }

    public function insertDeleteReason($user_id, $reason){
        $data = array(
            'user_id' => $user_id,
            'delete_reason' => $reason
        );
        $this->db->insert('delete_reason', $data);
        return $this->db->insert_id();
    }

    public function disableUserAccount($token){
        $this->db->update('users', ['status' => 'Disable', 'online_status' => 'Offline'], ['token' => $token]);
        return $this->db->affected_rows();
    }
    public function enableUserAccount($user_id){
        $this->db->update('users', ['status' => 'Active', 'online_status' => 'Online'], ['id' => $user_id]);
        return $this->db->affected_rows();
    }

    public function checkHobby($hobby){
        $this->db->select('hobbies.id');
        $this->db->from('hobbies');
        $this->db->where(['name' => $hobby, 'status!= ' => 'Deleted']);
        $result = $this->db->get();
        return $result->row_array();
    }

    public function add_hobby($user_id){
        $data=array(
            'user_id'          =>$user_id,
            'name'          =>trim($this->input->post('hobby')),
            'created_at'    =>date('Y-m-d H:i:s'),
        );
        $this->db->insert('hobbies',$data);
        $id=$this->db->insert_id();
        $query = $this->db->get_where('hobbies', ['id' =>$id ]);
        return $query->row_array();
    }

    public function updateUserData($token){
        $data = array(
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'profession' => $this->input->post('profession'),
            'age' => $this->input->post('age'),
            'about_me' => $this->input->post('about_me')
        );
        $this->db->update('users', $data, ['token' => $token]);
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function updateLocation($token){
        $data = array(
            'location' => $this->input->post('location'),
            'latitude' => $this->input->post('latitude'),
            'longitude' => $this->input->post('longitude')
        );
        $this->db->update('users', $data, ['token' => $token]);
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function contactUs($user_id){
        $data = array(
            'user_id'       => $user_id,
            'feature'       => $this->input->post('feature'),
            'feedback'      => $this->input->post('feedback'),
            'created_at'    => date('Y-m-d H:i:s')
        );
        $this->db->insert('contact_us', $data);
        $id = $this->db->insert_id();
        $result = $this->db->get_where('contact_us', ['id' => $id]);
        return $result->row_array();
    }

    public function checkLikeAvailability($visitor_id, $user_id, $type){
        $this->db->select('*');
        $this->db->from('liked');
        $this->db->where(['user_id'=>$user_id, 'visitor_id'=>$visitor_id, 'liked_status' => $type]);
        $query = $this->db->get();
        return $query->row_array();
    }
    
    public function checkBlockUser($visitor_id, $user_id){
        $this->db->select('*');
        $this->db->from('block_user');
        $this->db->where(['user_id'=> $user_id, 'blocked_user_id'=> $visitor_id]);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function visitProfile($visitor_id, $user_id){
        $data = array(
            'user_id' => $user_id,
            'visitor_id' => $visitor_id,
            'liked_status' => 'visited'
        );
        $this->db->insert('liked', $data);
        return $this->db->insert_id();
    }

    public function blockUserProfile($visitor_id, $user_id){
        $data = array(
            'user_id' => $user_id,
            'blocked_user_id' => $visitor_id
        );
        $this->db->insert('block_user', $data);
        return $this->db->insert_id();
    }
    
    public function likeUserProfile($visitor_id, $user_id){
        $data = array(
            'user_id' => $user_id,
            'visitor_id' => $visitor_id,
            'liked_status' => 'liked'
        );
        $this->db->insert('liked', $data);
        return $this->db->insert_id();
    }

    public function updateLikeUserProfile($visitor_id, $user_id){
        $data = array(
            'liked_status' => 'liked'
        );
        $this->db->update('liked', $data, ['user_id'=>$user_id, 'visitor_id'=>$visitor_id, 'liked_status' => 'unliked']);
        return true;
    }

    public function unlikeUserProfile($visitor_id, $user_id){
        $data = array(
            'liked_status' => 'unliked'
        );
        $this->db->update('liked', $data, ['user_id'=>$user_id, 'visitor_id'=>$visitor_id, 'liked_status' => 'liked']);
        return true;
    }

    public function insertUnlikeUserProfile($visitor_id, $user_id){
        $data = array(
            'user_id' => $user_id,
            'visitor_id' => $visitor_id,
            'liked_status' => 'unliked'
        );
        $this->db->insert('liked', $data);
        return $this->db->insert_id();
    }
    
    public function unblockUserProfile($visitor_id, $user_id){
        $this->db->delete('block_user', ['user_id'=>$user_id, 'blocked_user_id'=>$visitor_id]);
        return true;
    }

    public function getProfileDetail($visitor_id){
        $this->db->select('users.id, users.first_name, users.last_name, users.age, users.religion, users.profession, users.location, users.country_flag, users.country_code, users.phone, users.about_me, users.show_gender, users.image_url');
        $this->db->from('users');
        $this->db->where('users.id', $visitor_id);
        $this->db->where('users.status', 'Active');
        $result = $this->db->get();
        return $result->row_array();
    }

    public function getNassiboYouList($user_id, $type){
        $this->db->select('users.id, users.first_name, users.last_name, users.image_url, users.age, users.country_flag, users.profession, users.created_at');
        $this->db->from('liked');
        $this->db->join('users', 'users.id = liked.user_id');
        $this->db->where('liked.visitor_id', $user_id);
        $this->db->where('liked.liked_status', $type);
        $this->db->order_by('id', 'desc');
        $this->db->where('users.status !=','Deleted');
        $this->db->where('users.status !=','Disable');
        $this->db->limit(5);
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getYouNassiboList($user_id, $type){
        $this->db->select('users.id, users.first_name, users.last_name, users.image_url, users.age, users.country_flag, users.profession, liked.created_at');
        $this->db->from('liked');
        $this->db->join('users', 'users.id = liked.visitor_id');
        $this->db->where('liked.user_id', $user_id);
        $this->db->where('liked.liked_status', $type);
        $this->db->order_by('id', 'desc');
        $this->db->where('users.status !=','Deleted');
        $this->db->where('users.status !=','Disable');
        $this->db->limit(5);
        $result = $this->db->get();
        return $result->result_array();
    }

    public function updateAgeDistanceRange($token, $age_range, $distance_range, $distance_unit, $global, $interested_in){
        if(!empty($this->input->post('global'))){
            $update_global = $this->input->post('global');
        }else{
            $update_global = $global;
        }
        if(!empty($this->input->post('distance_range'))){
            $update_distance_range = $this->input->post('distance_range');
        }else{
            $update_distance_range = $distance_range;
        }
        if(!empty($this->input->post('age_range'))){
            $update_age_range = $this->input->post('age_range');
        }else{
            $update_age_range = $age_range;
        }
        if(!empty($this->input->post('distance_unit'))){
            $update_distance_unit = $this->input->post('distance_unit');
        }else{
            $update_distance_unit = $distance_unit;
        }
        if(!empty($this->input->post('interested_in'))){
            $update_interested_in = $this->input->post('interested_in');
        }else{
            $update_interested_in = $interested_in;
        }
        $data = array(
            'global' => $update_global,
            'distance_range' => $update_distance_range,
            'distance_unit' => $update_distance_unit,
            'age_range' => $update_age_range,
            'age_range_status' => $this->input->post('age_range_status'),
            'interested_in' => $update_interested_in
        );
        $this->db->update('users', $data, ['token' => $token]);
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function updateAppLanguage($token){
        $data = array(
            'app_language' => $this->input->post('app_language'),
        );
        $this->db->update('users', $data, ['token' => $token]);
        $result = $this->db->get_where('users', ['token' => $token]);
        return $result->row_array();
    }

    public function getUserAppLanguage($token){
        $this->db->select('languages.name as language_name');
        $this->db->from('languages');
        $this->db->join('users', 'users.app_language = languages.language_code');
        $this->db->where('users.token', $token);
        $result = $this->db->get();
        return $result->row();
    }

    public function getLikedUsersList($user_id, $liked, $unliked=null){
        $this->db->select('liked.visitor_id');
        $this->db->from('liked');
        $this->db->where(['liked.user_id' => $user_id, 'liked.liked_status' => $liked]);
        $query = $this->db->get();
        $result = $query->result_array();
        if(empty($result)){
            $this->db->select('liked.visitor_id');
            $this->db->from('liked');
            $this->db->where(['liked.user_id' => $user_id, 'liked.liked_status' => $unliked]);
            $query = $this->db->get();
            return $query->result_array();
        }else{
            return $result;
        }
    }

    public function getNassiboUsersList($token, $latitude, $longitude, $user_age_range_status, $user_age_range, $interested_in, $age_range, $location_range, $religion, $religion_specify, $habits, $language, $user_hobbies, $liked_user_id, $user_id){
        $this->db->select('users.id, users.first_name, users.last_name, users.age, users.gender, users.profession, users.country_code, users.country_flag, users.phone, users.profession, users.religion, users.image_url, users.about_me, users.location, users.latitude, users.longitude,users.online_status');

        $this->db->from('users');
        if(!empty($interested_in)){
            $this->db->where(['users.gender' => $interested_in]);
        }
        if(!empty($religion[0])){
            $this->db->where_in('users.religion', $religion);
        }
        if(!empty($religion_specify[0])){
            $this->db->where_in('users.specify_religion', $religion_specify);
        }
        if(!empty($habits[0])){
            $this->db->join('user_habits', 'user_habits.user_id = users.id');
            $this->db->where_in('user_habits.habits', $habits);
        }
        if(!empty($language[0])){
            $this->db->join('user_languages', 'user_languages.user_id = users.id');
            $this->db->where_in('user_languages.language_id', $language);
        }
        if(!empty($age_range)){
            $age = explode('-', $age_range);
            $this->db->where('users.age >=', $age[0]);
            $this->db->where('users.age <=', $age[1]);
        }elseif(!empty($user_age_range) && $user_age_range_status != 'no'){
            $age = explode('-', $user_age_range);
            $this->db->where('users.age >=', $age[0]);
            $this->db->where('users.age <=', $age[1]);
        }
        if(!empty($liked_user_id)){
            $this->db->where_not_in('users.id', $liked_user_id);
        }
        $this->db->join('user_hobbies', 'user_hobbies.user_id = users.id');
        $this->db->where_in('user_hobbies.hobbies_name', $user_hobbies);
        $this->db->where(['users.status' => 'Active', 'users.blocked' => 'no', 'users.id!=' => $user_id]);
        $this->db->group_by('users.id');
        $result = $this->db->get();
        return $result->result_array();
    }

    public function reportUser($user_id, $visitor_id){
        $report_reason = $this->input->post('report_reason');
        $data = array(
            'user_id'           => $user_id,
            'reported_user_id'  => $visitor_id,
            'report_reason'     => $report_reason,
            'description'       => $this->input->post('description')
        );
        $this->db->insert("report_user", $data);
        return $this->db->insert_id();
    }

    public function visitorReportCount($visitor_id){
        $this->db->select('report_user.id as report_count');
        $this->db->from('report_user');
        $this->db->where('report_user.reported_user_id', $visitor_id);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function sendReportUserNotification($visitor_id){
        $this->db->select('users.first_name, users.last_name');
        $users = $this->db->get_where('users', ['id' => $visitor_id]);
        $result = $users->row_array();
        $data = array(
            'reported_user_id'  => $visitor_id,
            'title' => "User Reported.",
            'message' => 'User "'.$result['first_name'].' '.$result['last_name'].'" has been reported.'
        );
        $this->db->insert('notification', $data);
        return $this->db->insert_id();
    }

    public function checkChatUser($visitor_id, $user_id){
        $this->db->select('*');
        $this->db->from('chat_user');
        $this->db->where(['user_id'=> $user_id, 'reciever_id'=> $visitor_id]);
        $this->db->order_by('id','desc');
        $query = $this->db->get();
        $result = $query->row_array();
        if(empty($result)){
            $this->db->select('*');
            $this->db->from('chat_user');
            $this->db->where(['user_id'=> $visitor_id, 'reciever_id'=> $user_id]);
            $this->db->order_by('id','desc');
            $query = $this->db->get();
            return $query->row_array();
        }else{
            return $result;
        }
    }

    public function insertChatUser($visitor_id, $user_id){
        $data = array(
            'user_id' => $user_id,
            'reciever_id' => $visitor_id
        );
        $this->db->insert('chat_user', $data);
        return $this->db->insert_id();
    }

    public function insertMessage($chat_id, $visitor_id, $user_id, $message, $media_url){
        if(!empty($media_url)){
            $attachment = 'yes';
        }else{
            $attachment = 'no';
        }
        $data = array(
            'chat_id' => $chat_id,
            'sender_id' => $user_id,
            'reciever_id' => $visitor_id,
            'message' => $message,
            'attachment' => $attachment,
            'date' => date('Y-m-d'),
        );
        $this->db->insert('chat', $data);
        $chat_message_id = $this->db->insert_id();

        if(!empty($media_url)){
            foreach($media_url as $value){
                $media_data = array(
                    'chat_message_id' => $chat_message_id,
                    'file' => $value
                );
                $this->db->insert('chat_attachment', $media_data);
            }
            return $this->db->insert_id();
        }else{
            return $chat_message_id;
        }
    }

    public function checkChat($user_id){
        $this->db->select('*');
        $this->db->from('chat_user c');
        $this->db->where('user_id', $user_id);
        //$this->db->where('status', 'Active');
        $this->db->or_where('reciever_id', $user_id);
        $this->db->order_by('created_at','desc');
        $sel = $this->db->get();
        return $result = $sel->result_array();
    }

    public function checkChatData($chat_id){
        $this->db->select('*');
        $this->db->from('chat_user');
        $this->db->where('id', $chat_id);
        return $this->db->get()->row_array();
    }

    public function getChatMessages($chat_id){
        $this->db->select('chat.*');
        $this->db->from('chat');
        $this->db->where('chat.chat_id', $chat_id);
        $this->db->order_by('chat.id', 'desc');
        $result = $this->db->get();
        return $result->row_array();
    }

    public function getChatUserDetail($user_id){
        $this->db->select('users.id, users.first_name, users.last_name, users.image_url');
        $this->db->from('users');
        $this->db->where('users.id', $user_id);
        $result = $this->db->get();
        return $result->row_array();
    }

    public function getAllChatDates($chat_id){
        $this->db->select('chat.date');
        $this->db->from('chat');
        $this->db->where('chat.chat_id', $chat_id);
        $this->db->group_by('chat.date');
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getAllChatMessages($date, $chat_id){
        $this->db->select('chat.id,chat.chat_id,chat.sender_id,chat.reciever_id,chat.message,chat.attachment,chat.date,chat.created_at');
        $this->db->from('chat');
        $this->db->where(['chat.chat_id' => $chat_id, 'date' => $date]);
        // $this->db->where('chat.message !=', NULL);
        // $this->db->where('chat.message !=', '');
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getAttachedFiles($chat_id){
        $this->db->select('chat_attachment.file');
        $this->db->from('chat');
        $this->db->join('chat_attachment', 'chat_attachment.chat_message_id = chat.id');
        $this->db->where(['chat.id' => $chat_id]);
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getUserDataMessages($user_id){
        $this->db->select('users.first_name, users.last_name, users.image_url, users.online_status');
        $this->db->from('users');
        $this->db->where('users.id', $user_id);
        $this->db->where('users.status', 'Active');
        $result = $this->db->get();
        return $result->row_array();
    }

    public function checkSocialId($social_id){
        $result = $this->db->get_where('users', ['social_id' => $social_id, 'status !=' => 'Deleted']);
        return $result->row_array();
    }

    public function checkBlockedUser($blocked_user_id){
        $this->db->select('*');
        $this->db->from('block_user');
        $this->db->where(['blocked_user_id'=> $blocked_user_id]);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function checkBlockedUserMessages($blocked_user_id, $user_id){
       
        $this->db->select('*');
        $this->db->from('block_user');
        $this->db->where(['blocked_user_id'=> $blocked_user_id, 'user_id'=> $user_id]);
        $this->db->or_where(['user_id'=> $blocked_user_id, 'blocked_user_id'=> $user_id]);
        $query = $this->db->get();
        $result = $query->result_array();
        if(empty($result)){
            $this->db->select('*');
            $this->db->from('block_user');
            $this->db->where(['user_id'=> $blocked_user_id, 'blocked_user_id'=> $user_id]);
            $this->db->or_where(['blocked_user_id'=> $blocked_user_id, 'user_id'=> $user_id]);
            $query = $this->db->get();
            return $query->result_array();
        }else{
            return $result;
        }
    }
    public function checkBlockedStatus($blocked_user_id, $user_id){
       
        $this->db->select('*');
        $this->db->from('block_user');
        $this->db->where(['blocked_user_id'=> $blocked_user_id, 'user_id'=> $user_id]);
        //$this->db->or_where(['user_id'=> $blocked_user_id, 'blocked_user_id'=> $user_id]);
        $query = $this->db->get();
        $result = $query->result_array();
        if(empty($result)){
            $this->db->select('*');
            $this->db->from('block_user');
            $this->db->where(['user_id'=> $blocked_user_id, 'blocked_user_id'=> $user_id]);
            $query = $this->db->get();
            return $query->result_array();
        }else{
            return $result;
        }
    }

    public function getUserStatus($user_id){
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id', $user_id);
        $query = $this->db->get();
        return $query->row_array();
    }
}
?>