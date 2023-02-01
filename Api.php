<?php 
defined('BASEPATH') or exit ('No direct script access allowed');
require APPPATH.'third_party/cloudinary/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
/**
 * 
 */
class Api extends CI_Controller{
	
	public function __construct(){
		parent::__construct();
		$this->load->model('Api_model');
	}

	/*Unique Id Starts*/
	public function token() {
		$str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNIPQRSTUVWXYZ';
		$nstr = str_shuffle($str);
		$token = substr($nstr, 0, 10);
		return $token;
	}
	/*Unique Id Ends*/

	/*Upload Single Image Starts*/
    public function doUploadImage($file_name){
        Configuration::instance([
            'cloud' => [
                "cloud_name" => $this->config->item('cloudinary_cloud'), 
                "api_key" => $this->config->item('cloudinary_api_key'), 
                "api_secret" => $this->config->item('cloudinary_api_sercret')],
                'url' => [
                    'secure' => true
                ]
            ]);
        $profile_image = (new UploadApi())->upload($file_name, [
            'resource_type' => 'image',
            'public_id' => 'nassibo/'.rand(1111, 9999),
            'chunk_size' => 6000000,
            'eager_async' => true]
        );
        $img = json_encode($profile_image);
        $image = json_decode($img);
        $profile_image = $image->secure_url;
        return $profile_image;
    }
    /*Upload Single Image Ends*/

    /*Upload Multiple Image Starts*/
    public function upload_files($file_name){
        Configuration::instance([
            'cloud' => [
                "cloud_name" => $this->config->item('cloudinary_cloud'), 
                "api_key" => $this->config->item('cloudinary_api_key'), 
                "api_secret" => $this->config->item('cloudinary_api_sercret')],
                'url' => [
                    'secure' => true
                ]
            ]);
        $i = 0;
        foreach($file_name as $images_temp_name){
            $multiple_images = (new UploadApi())->upload($images_temp_name, [
                'resource_type' => 'auto',
                'public_id' => 'nassibo/'.rand(1111, 9999),
                'chunk_size' => 6000000,
                'eager_async' => true]
            );
            $img = json_encode($multiple_images);
            $image = json_decode($img);
            $user_images[$i] = $image->secure_url;
            $i++;
        }
        return $user_images;
    }
    /*Upload Multiple Image Ends*/

	/*Languages List Api Start*/
	public function getLanguages(){
		$this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
		if(!empty($user_data)){
			if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
	            header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
	            $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
	            return false;
			}
        }

        $result = $this->Api_model->getAllLanguages();
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
	}
	/*Languages List Api End*/
	
	/*Filter List Api Start*/
    public function getFilterList(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        $result['belief'] = $this->Api_model->getAllReligion();
        $i = 0;
        foreach ($result as $key => $value) {
            if(!empty($value['image_url'])){
                $explode = explode('upload', $value['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['belief'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['belief'][$i]['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            $i++;
        }
        $result['belief_specifications'] = $this->Api_model->getAllBeliefSpecification();
        $result['habits'] = $this->Api_model->getAllHabits();
        $i = 0;
        foreach ($result as $key => $value) {
            if(!empty($value['image_url'])){
                $explode = explode('upload', $value['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['habits'][$i]['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            $i++;
        }
        $result['languages'] = $this->Api_model->getAllLanguages();
		$result['age_range'] = $user_data['age_range'];
		if(!empty($user_data['distance_range'])){
        	$result['distance_range'] = $user_data['distance_range'];
		}else{
			$result['distance_range'] = "0";
		}
        $result['distance_unit'] = $user_data['distance_unit'];
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
    }
    /*Filter List Api End*/

    /*Habits List Api Start*/
    public function getHabits(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $result = $this->Api_model->getAllHabits();
        $i = 0;
        foreach ($result as $key => $value) {
            if(!empty($value['image_url'])){
                $explode = explode('upload', $value['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result[$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result[$i]['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            $i++;
        }
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
    }
    /*Habits List Api End*/

    /*Hobbies List Api Start*/
    public function getHobbies(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $result = [];
        $null_result = $this->Api_model->getAllHobbies();
        $user_result = $this->Api_model->getAllUserHobbies($user_data['id']);
        $result = array_merge($null_result, $user_result);
		
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
    }
    /*Hobbies List Api End*/

    /*Belief Specification List Api Start*/
    public function getBeliefSpecification(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $result = $this->Api_model->getAllBeliefSpecification();
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
    }
    /*Belief Specification List Api End*/

    /*House Rules List Api Start*/
    public function getHouseRules(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $result = $this->Api_model->getAllHouseRules();
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
    }
    /*House Rules List Api End*/

    /*Religion List Api Start*/
    public function getAllReligion(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $result = $this->Api_model->getAllReligion();
        $i = 0;
        foreach ($result as $key => $value) {
            if(!empty($value['image_url'])){
                $explode = explode('upload', $value['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result[$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result[$i]['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            $i++;
        }
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
    }
    /*Religion List Api End*/

    /*Get Pages API Starts*/
    public function getPages(){
        $this->output->set_content_type('application/json');

        $page_id = $this->input->post('page');
        $result = $this->Api_model->getPages($page_id);
        if ($result) {
            $str = ["&nbsp;", "&#39;"];
            $rplc = [" ", "'"];
            $description = str_replace($str, $rplc, $result['description']);
            $result['description'] = $description;
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => -1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
    }
    /*Get Pages API Ends*/

    /*Get Report Reasons API Starts*/
    public function getReportReasons(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        
        $result = $this->Api_model->getReportReasons();
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
    }
    /*Get Report Reasons API Ends*/

    /*Add Hobby Api Start*/
    public function addHobby(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        $result=$this->Api_model->add_hobby($user_data['id']);
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Hobby Added Successfully.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
    }
    /*Add Hobby Api End*/

    /*Login API Starts*/
    public function login(){
		$this->output->set_content_type('application/json');
		$key = $this->input->post('key');
		$signup_type = $this->input->post('type');
		$country_code = $this->input->post('country_code');
        $country_flag = $this->input->post('country_flag');
        $phone = $this->input->post('phone');
		$email = $this->input->post('email');
        $social_id = $this->input->post('social_id');
        $social_type = $this->input->post('social_type');
		$recieved_otp = $this->input->post('otp');
		$token = $this->token();
		$otp = '1234';
        if(empty($this->input->post('social_id'))){
    		if($key === 'otp'){
    			if($signup_type === 'email'){
                    if(empty($email)){
                        $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Please Enter Email.' , 'data' => null]));
                        return false;
                    }
    				$checkmail = $this->Api_model->checkUserData($signup_type, NULL, $email);
    				if(!empty($checkmail)){
                        if($checkmail['blocked'] == 'yes'){
                            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Your account has been blocked.' , 'data' => NULL]));
                            return FALSE;
                        }else if($checkmail['status'] == 'Inactive'){
                            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Your account has been Inactive.' , 'data' => NULL]));
                            return FALSE;
                        }else{
    					   $update_otp = $this->Api_model->updateOtp($signup_type, $otp, NULL, $email, $token);
                        }
    				}else{
    					$insert_otp = $this->Api_model->insertOtp($signup_type, $otp, NULL, NULL, $email);
    				}
                    $result['email'] = $email;
                    $result['otp'] = $otp;
    			}elseif ($signup_type === 'phone') {
                    if(empty($country_code)){
                        $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Please Enter Country Code.' , 'data' => null]));
                        return false;
                    }
                    if(empty($phone)){
                        $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Please Enter Phone Number.' , 'data' => null]));
                        return false;
                    }
    				$checkphone = $this->Api_model->checkUserData($signup_type, $country_code, $phone);
    				if(!empty($checkphone)){
                        if($checkphone['status'] == 'disable'){
                             $this->Api_model->enableUserAccount($checkphone['id']);
                        }           
                        if($checkphone['blocked'] == 'yes'){
                            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Your account has been blocked.' , 'data' => NULL]));
                            return FALSE;
                        }else if($checkphone['status'] == 'Inactive'){
                            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Your account has been Inactive.' , 'data' => NULL]));
                            return FALSE;
                        }else{
    					   $update_otp = $this->Api_model->updateOtp($signup_type, $otp, $country_code, $phone, $token);
                        }
    				}else{
    					$insert_otp = $this->Api_model->insertOtp($signup_type, $otp, $country_code, $country_flag, $phone);
    				}
                    $result['country_code'] = $country_code;
                    $result['phone'] = $phone;
                    $result['country_flag'] = $country_flag;
                    $result['otp'] = $otp;
    			}
                $this->output->set_output(json_encode(['result' => 1, 'msg' =>'OTP Sent Successfully.' , 'data' => $result]));
                return FALSE;
    		}else if($key === 'verify'){
    			if($signup_type == 'email'){
                    $checkmail = $this->Api_model->checkUserData($signup_type, NULL, $email);
                    if(!empty($checkmail)){
    				    $verify_otp = $this->Api_model->verifyOtp($signup_type, NULL, $email, $recieved_otp);
                        if(!empty($verify_otp)){
                            $updateOTPField = $this->Api_model->updateOtpField($signup_type, NULL, NULL, $email);
                            if(!empty($updateOTPField['specify_religion'])){
                                $updateOTPField['religion_specification'] = $this->Api_model->getReligionSpecification($updateOTPField['token'])->religion_specification;
                            }else{
                                $updateOTPField['religion_specification'] = NULL;
                            }
                            if(!empty($updateOTPField['image_url'])){
                                $explode = explode('upload', $updateOTPField['image_url']);
                                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                    $height = 'h_'.$this->input->get('height');
                                    $width = 'w_'.$this->input->get('width');
                                }else{
                                    $height = 'h_200';
                                    $width = 'w_200';
                                }
                                $updateOTPField['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                            }else{
                                $updateOTPField['image_url'] = base_url('assets/admin/no_image_avail.png');
                            }
                            if(!empty($updateOTPField['app_language'])){
                                $updateOTPField['app_language_name'] = $this->Api_model->getUserAppLanguage($updateOTPField['token'])->language_name;
                            }else{
                                $updateOTPField['app_language_name'] = NULL;
                            }
                            $updateOTPField['habits'] = $this->Api_model->getUserHabits($updateOTPField['id']);
                            $i = 0;
                            foreach ($updateOTPField['habits'] as $key => $value) {
                                if(!empty($value['image_url'])){
                                    $explode = explode('upload', $value['image_url']);
                                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                        $height = 'h_'.$this->input->get('height');
                                        $width = 'w_'.$this->input->get('width');
                                    }else{
                                        $height = 'h_200';
                                        $width = 'w_200';
                                    }
                                    $updateOTPField['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                                }else{
                                    $updateOTPField['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                                }
                                $i++;
                            }
                            $updateOTPField['hobbies'] = $this->Api_model->getUserHobbies($updateOTPField['id']);
                            $updateOTPField['user_images'] = $this->Api_model->getUserImages($updateOTPField['id']);
                            $i = 0;
                            foreach ($updateOTPField['user_images'] as $key => $value) {
                                if(!empty($value['image_url'])){
                                    $explode = explode('upload', $value['image_url']);
                                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                        $height = 'h_'.$this->input->get('height');
                                        $width = 'w_'.$this->input->get('width');
                                    }else{
                                        $height = 'h_200';
                                        $width = 'w_200';
                                    }
                                    $updateOTPField['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                                }else{
                                    $updateOTPField['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                                }
                                $i++;
                            }
                            $updateOTPField['user_languages'] = $this->Api_model->getUserLanguages($updateOTPField['id']);
                            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'OTP Verified Successfully.' , 'data' => $updateOTPField]));
                        }else{
                            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'OTP Mismatched.' , 'data' => 'OTP Mismatched.']));
                        }
                        return FALSE;
                    }else{
                        $getUserOTPData = $this->Api_model->getUserOTPData($signup_type, NULL, $email);
                        $verify_otp = $this->Api_model->verifyNewUserOtp($signup_type, NULL, $email, $recieved_otp);
                        if(!empty($verify_otp)){
                            $insertOTPField = $this->Api_model->insertOtpField($signup_type, NULL, NULL, $email, $token);
                            if(!empty($insertOTPField['specify_religion'])){
                                $insertOTPField['religion_specification'] = $this->Api_model->getReligionSpecification($insertOTPField['token'])->religion_specification;
                            }else{
                                $insertOTPField['religion_specification'] = NULL;
                            }
                            if(!empty($insertOTPField['image_url'])){
                                $explode = explode('upload', $insertOTPField['image_url']);
                                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                    $height = 'h_'.$this->input->get('height');
                                    $width = 'w_'.$this->input->get('width');
                                }else{
                                    $height = 'h_200';
                                    $width = 'w_200';
                                }
                                $insertOTPField['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                            }else{
                                $insertOTPField['image_url'] = base_url('assets/admin/no_image_avail.png');
                            }
                            if(!empty($insertOTPField['app_language'])){
                                $insertOTPField['app_language_name'] = $this->Api_model->getUserAppLanguage($insertOTPField['token'])->language_name;
                            }else{
                                $insertOTPField['app_language_name'] = NULL;
                            }
                            $insertOTPField['habits'] = $this->Api_model->getUserHabits($insertOTPField['id']);
                            $i = 0;
                            foreach ($insertOTPField['habits'] as $key => $value) {
                                if(!empty($value['image_url'])){
                                    $explode = explode('upload', $value['image_url']);
                                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                        $height = 'h_'.$this->input->get('height');
                                        $width = 'w_'.$this->input->get('width');
                                    }else{
                                        $height = 'h_200';
                                        $width = 'w_200';
                                    }
                                    $insertOTPField['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                                }else{
                                    $insertOTPField['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                                }
                                $i++;
                            }
                            $insertOTPField['hobbies'] = $this->Api_model->getUserHobbies($insertOTPField['id']);
                            $insertOTPField['user_images'] = $this->Api_model->getUserImages($insertOTPField['id']);
                            $i = 0;
                            foreach ($insertOTPField['user_images'] as $key => $value) {
                                if(!empty($value['image_url'])){
                                    $explode = explode('upload', $value['image_url']);
                                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                        $height = 'h_'.$this->input->get('height');
                                        $width = 'w_'.$this->input->get('width');
                                    }else{
                                        $height = 'h_200';
                                        $width = 'w_200';
                                    }
                                    $insertOTPField['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                                }else{
                                    $insertOTPField['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                                }
                                $i++;
                            }
                            $insertOTPField['user_languages'] = $this->Api_model->getUserLanguages($insertOTPField['id']);
                            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'OTP Verified Successfully.' , 'data' => $insertOTPField]));
                        }else{
                            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'OTP Mismatched.' , 'data' => 'OTP Mismatched.']));
                        }
                        return FALSE;
                    }
    			}else{
                    $checkphone = $this->Api_model->checkUserData($signup_type, $country_code, $phone);
    				if(!empty($checkphone)){
                        $verify_otp = $this->Api_model->verifyOtp($signup_type, $country_code, $phone, $recieved_otp);
                        if(!empty($verify_otp)){
                            $updateOTPField = $this->Api_model->updateOtpField($signup_type, $country_code, $country_flag, $phone);
                            if(!empty($updateOTPField['specify_religion'])){
                                $updateOTPField['religion_specification'] = $this->Api_model->getReligionSpecification($updateOTPField['token'])->religion_specification;
                            }else{
                                $updateOTPField['religion_specification'] = NULL;
                            }
                            if(!empty($updateOTPField['image_url'])){
                                $explode = explode('upload', $updateOTPField['image_url']);
                                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                    $height = 'h_'.$this->input->get('height');
                                    $width = 'w_'.$this->input->get('width');
                                }else{
                                    $height = 'h_200';
                                    $width = 'w_200';
                                }
                                $updateOTPField['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                            }else{
                                $updateOTPField['image_url'] = base_url('assets/admin/no_image_avail.png');
                            }
                            if(!empty($updateOTPField['app_language'])){
                                $updateOTPField['app_language_name'] = $this->Api_model->getUserAppLanguage($updateOTPField['token'])->language_name;
                            }else{
                                $updateOTPField['app_language_name'] = NULL;
                            }
                            $updateOTPField['habits'] = $this->Api_model->getUserHabits($updateOTPField['id']);
                            $i = 0;
                            foreach ($updateOTPField['habits'] as $key => $value) {
                                if(!empty($value['image_url'])){
                                    $explode = explode('upload', $value['image_url']);
                                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                        $height = 'h_'.$this->input->get('height');
                                        $width = 'w_'.$this->input->get('width');
                                    }else{
                                        $height = 'h_200';
                                        $width = 'w_200';
                                    }
                                    $updateOTPField['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                                }else{
                                    $updateOTPField['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                                }
                                $i++;
                            }
                            $updateOTPField['hobbies'] = $this->Api_model->getUserHobbies($updateOTPField['id']);
                            $updateOTPField['user_images'] = $this->Api_model->getUserImages($updateOTPField['id']);
                            $i = 0;
                            foreach ($updateOTPField['user_images'] as $key => $value) {
                                if(!empty($value['image_url'])){
                                    $explode = explode('upload', $value['image_url']);
                                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                        $height = 'h_'.$this->input->get('height');
                                        $width = 'w_'.$this->input->get('width');
                                    }else{
                                        $height = 'h_200';
                                        $width = 'w_200';
                                    }
                                    $updateOTPField['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                                }else{
                                    $updateOTPField['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                                }
                                $i++;
                            }
                            $updateOTPField['user_languages'] = $this->Api_model->getUserLanguages($updateOTPField['id']);
                            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'OTP Verified Successfully.' , 'data' => $updateOTPField]));
                        }else{
                            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'OTP Mismatched.' , 'data' => 'OTP Mismatched.']));
                        }
                        return FALSE;
                    }else{
                        $getUserOTPData = $this->Api_model->getUserOTPData($signup_type, $country_code, $phone);
                        $verify_otp = $this->Api_model->verifyNewUserOtp($signup_type, $country_code, $phone, $recieved_otp);
                        if(!empty($verify_otp)){
                            $insertOTPField = $this->Api_model->insertOtpField($signup_type, $country_code, $country_flag, $phone, $token);
                            if(!empty($insertOTPField['specify_religion'])){
                                $insertOTPField['religion_specification'] = $this->Api_model->getReligionSpecification($insertOTPField['token'])->religion_specification;
                            }else{
                                $insertOTPField['religion_specification'] = NULL;
                            }
                            if(!empty($insertOTPField['image_url'])){
                                $explode = explode('upload', $insertOTPField['image_url']);
                                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                    $height = 'h_'.$this->input->get('height');
                                    $width = 'w_'.$this->input->get('width');
                                }else{
                                    $height = 'h_200';
                                    $width = 'w_200';
                                }
                                $insertOTPField['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                            }else{
                                $insertOTPField['image_url'] = base_url('assets/admin/no_image_avail.png');
                            }
                            if(!empty($insertOTPField['app_language'])){
                                $insertOTPField['app_language_name'] = $this->Api_model->getUserAppLanguage($insertOTPField['token'])->language_name;
                            }else{
                                $insertOTPField['app_language_name'] = NULL;
                            }
                            $insertOTPField['habits'] = $this->Api_model->getUserHabits($insertOTPField['id']);
                            $i = 0;
                            foreach ($insertOTPField['habits'] as $key => $value) {
                                if(!empty($value['image_url'])){
                                    $explode = explode('upload', $value['image_url']);
                                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                        $height = 'h_'.$this->input->get('height');
                                        $width = 'w_'.$this->input->get('width');
                                    }else{
                                        $height = 'h_200';
                                        $width = 'w_200';
                                    }
                                    $insertOTPField['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                                }else{
                                    $insertOTPField['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                                }
                                $i++;
                            }
                            $insertOTPField['hobbies'] = $this->Api_model->getUserHobbies($insertOTPField['id']);
                            $insertOTPField['user_images'] = $this->Api_model->getUserImages($insertOTPField['id']);
                            $i = 0;
                            foreach ($insertOTPField['user_images'] as $key => $value) {
                                if(!empty($value['image_url'])){
                                    $explode = explode('upload', $value['image_url']);
                                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                        $height = 'h_'.$this->input->get('height');
                                        $width = 'w_'.$this->input->get('width');
                                    }else{
                                        $height = 'h_200';
                                        $width = 'w_200';
                                    }
                                    $insertOTPField['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                                }else{
                                    $insertOTPField['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                                }
                                $i++;
                            }
                            $insertOTPField['user_languages'] = $this->Api_model->getUserLanguages($insertOTPField['id']);
                            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'OTP Verified Successfully.' , 'data' => $insertOTPField]));
                        }else{
                            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'OTP Mismatched.' , 'data' => 'OTP Mismatched.']));
                        }
                        return FALSE;
                    }
    			}
    		}
        }else{
            $insert_social_data = $this->Api_model->insertSocialUserData($social_type, $email, $token, $social_id);
            if($insert_social_data){
                $result = $this->userFullData($token);
                $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Logged In Successfully.', 'data' => $result]));
                return FALSE;
            }
        }
	}
    /*Login API Ends*/

    /*Social Login API Starts*/
    public function sociallogin(){
        $this->output->set_content_type('application/json');
        $social_type = $this->input->post('social_type');
        $email = $this->input->post('email');
        $social_id = $this->input->post('social_id');
        $token = $this->token();
        if(empty($this->input->post('email'))){
            $checkSocialId = $this->Api_model->checkSocialId($social_id);
            if(empty($checkSocialId)){
                $this->output->set_output(json_encode(['result' => 402, 'msg' => "Social ID doesn't exists.", 'data' => NULL]));
                return FALSE;
            }else{
                $uncompletedData = $this->userFullData($checkSocialId['token']);
                if($checkSocialId['step'] != 'completed'){
                    $this->output->set_output(json_encode(['result' => 1, 'msg' => "Data Found.", 'data' => $uncompletedData]));
                    return FALSE;
                }else{
                    $this->output->set_output(json_encode(['result' => 1, 'msg' => "Data Found.", 'data' => $uncompletedData]));
                    return FALSE;
                }
            }
        }
        //$checkmail = $this->Api_model->checkSocialUserData($email);
        $checkmail = $this->Api_model->checkSocialUserDataByEmail($email);
      
        if(empty($checkmail)){
            $insert_social_data = $this->Api_model->insertSocialUserData($social_type, $email, $token, $social_id);
        }else{
            if($checkmail['blocked'] == 'yes'){
                $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Your account has been blocked.' , 'data' => NULL]));
                return FALSE;
            }else if($checkmail['status'] == 'Inactive'){
                $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Your account has been Inactive.' , 'data' => NULL]));
                return FALSE;
            }
            else{
                $update_social_data = $this->Api_model->updateSocialUserData($social_type, $email, $token, $social_id);
            }
        }
        $result = $this->Api_model->checkSocialUserData($token);
        if($result){
            if(!empty($result['specify_religion'])){
                $result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
            }else{
                $result['religion_specification'] = NULL;
            }
            if(!empty($result['image_url'])){
                $explode = explode('upload', $result['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            if(!empty($result['app_language'])){
                $result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
            }else{
                $result['app_language_name'] = NULL;
            }
            $result['habits'] = $this->Api_model->getUserHabits($result['id']);
            $i = 0;
            foreach ($result['habits'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
            $result['user_images'] = $this->Api_model->getUserImages($result['id']);
            $i = 0;
            foreach ($result['user_images'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Logged In Successfully.', 'data' => $result]));
            return FALSE;
        }else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => 'Something went wrong.']));
            return FALSE;
        }
    }
    /*Social Login API Ends*/

    /*Update Location API Starts*/
    public function updateLocation(){
        $this->output->set_content_type('application/json');
        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);

        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $result = $this->Api_model->updateLocation($token);
        if(!empty($result)){
            if(!empty($result['specify_religion'])){
                $result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
            }else{
                $result['religion_specification'] = NULL;
            }
            if(!empty($result['image_url'])){
                $explode = explode('upload', $result['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            if(!empty($result['app_language'])){
                $result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
            }else{
                $result['app_language_name'] = NULL;
            }
            $result['habits'] = $this->Api_model->getUserHabits($result['id']);
            $i = 0;
            foreach ($result['habits'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
            $result['user_images'] = $this->Api_model->getUserImages($result['id']);
            $i = 0;
            foreach ($result['user_images'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'Location Updated.' , 'data' => $result]));
        }else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Someting went wrong.' , 'data' => NULL]));
        }
        return FALSE;
    }
    /*Update Location API Ends*/

    /*Complete Profile API Starts*/
    public function completeProfile(){
    	$this->output->set_content_type('application/json');
    	$token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);

        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
		if(!empty($user_data)){
			if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
	            header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
	            $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
	            return false;
			}
        }
        
        $step = $user_data['step'];
        if(!empty($this->input->post('first_name')) || !empty($this->input->post('last_name'))){
        	$result = $this->Api_model->updateUserName($token);
        }else if(!empty($this->input->post('gender')) || !empty($this->input->post('age'))){
        	$result = $this->Api_model->updateGenderAge($token);
        }else if(!empty($this->input->post('belief')) || !empty($this->input->post('belief_specification')) || !empty($this->input->post('habits'))){
            $belief = $this->input->post('belief');
            $belief_specification = $this->input->post('belief_specification');
           
			$keytorep = ['"', "'", ' '];
            $keyrep = ['', '',''];
            
            $belief_specification=str_replace($keytorep, $keyrep,trim($belief_specification, '[]'));
           
            $de = str_replace($keytorep, $keyrep, explode(',', trim($this->input->post('habits'), '[]')));
                      
            $deleteHabits = $this->Api_model->deleteHabits($user_data['id']);
            $insertHabits = $this->Api_model->insertHabits($user_data['id'], $de);
            $result = $this->Api_model->updateBelief($belief, $belief_specification, $token);
        }else if(!empty($_FILES['image_url']['name']) || !empty($_FILES['user_images']['name'][0])){
        	if (!empty($_FILES['image_url']['name'])) {
	            $profile_image = $this->doUploadImage($_FILES['image_url']['tmp_name']);
	        } else {
	            $user = $this->Api_model->getUserById($user_data['id']);
	            $profile_image = $user['image_url'];
	        }

	        if (!empty($_FILES['user_images']['name'][0])) {
				$deleteuserimages = $this->Api_model->deleteUserImages(NULL, $user_data['id']);
	            $user_images = $this->upload_files($_FILES['user_images']['tmp_name']);
	        }
	        if (!empty($user_images)) {
                foreach ($user_images as $row) {
                    $this->Api_model->insertUserImages($row, $user_data['id']);
                }
            }
	        $result = $this->Api_model->updateUserProfileImage($profile_image, $token);
        }else if(!empty($this->input->post('interested_in'))){
        	$result = $this->Api_model->updateInterestedIn($token);
        }else if(!empty($this->input->post('hobbies')[0])){
			$keytorep = ['"', "'", ' '];
            $keyrep = ['', '',''];
            $de = str_replace($keytorep, $keyrep, explode(',', trim($this->input->post('hobbies'), '[]')));
			
        	$deleteHobbies = $this->Api_model->deleteHobbies($user_data['id']);
            $insertHobbies = $this->Api_model->insertHobbies($user_data['id'], $de);
            $result = $this->Api_model->updateUserStep($token);
        }else if(!empty($this->input->post('description'))){
            $result = $this->Api_model->updateDescription($token);
        }else if(empty($this->input->post('description'))){
            $result = $this->Api_model->updateDescription($token);
        }
        if($result){
            if(!empty($result['specify_religion'])){
                $result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
            }else{
                $result['religion_specification'] = NULL;
            }
            if(!empty($result['image_url'])){
                $explode = explode('upload', $result['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            if(!empty($result['app_language'])){
                $result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
            }else{
                $result['app_language_name'] = NULL;
            }
            $result['habits'] = $this->Api_model->getUserHabits($result['id']);
            $i = 0;
            foreach ($result['habits'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
            $result['user_images'] = $this->Api_model->getUserImages($result['id']);
            $i = 0;
            foreach ($result['user_images'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
        	$this->output->set_output(json_encode(['result' => 1, 'msg' => 'User Profile Updated.', 'data' => $result]));
            return FALSE;
        }else{
        	$this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => 'Something went wrong.']));
            return FALSE;
        }
    }
    /*Complete Profile API Ends*/

    /*Update Phone Number API Starts*/
    public function updatePhoneNumber(){
        $this->output->set_content_type("application/json");
        $token = $this->input->get_request_header('token');
        $key = $this->input->post('key');
        $country_code = $this->input->post('country_code');
        $country_flag = $this->input->post('country_flag');
        $phone = $this->input->post('phone');
        $otp = '1234';
        $user_data = $this->Api_model->getUserIdByToken($token);
    
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $checkPhone = $this->Api_model->checkPhoneNumber($country_code, $phone, $token);
        if(!empty($checkPhone)){
            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Phone Number is already registered.' , 'data' => NULL]));
            return false;
        }

        if($key === 'otp'){
            $result = [];
            $updatePhoneNumber = $this->Api_model->updateUserOtp($otp, $token);
            $result['id'] = $updatePhoneNumber['id'];
            $result['country_code'] = $country_code;
            $result['phone'] = $phone;
            $result['country_flag'] = $country_flag;
            $result['otp'] = $otp;
            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'OTP Sent Successfully.' , 'data' => $result]));
            return FALSE;
        }else if($key === 'verify'){
            $result = $this->Api_model->getUserData($token);
            if(!empty($result)){
                if(hash('sha256', $this->input->post('otp')) == $result['otp']){
                    $updatePhoneNumber = $this->Api_model->updatePhoneNumber($country_code, $country_flag, $phone, $token);
                    if(!empty($updatePhoneNumber['specify_religion'])){
                        $updatePhoneNumber['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
                    }else{
                        $updatePhoneNumber['religion_specification'] = NULL;
                    }
                    if(!empty($updatePhoneNumber['image_url'])){
                        $explode = explode('upload', $updatePhoneNumber['image_url']);
                        if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                            $height = 'h_'.$this->input->get('height');
                            $width = 'w_'.$this->input->get('width');
                        }else{
                            $height = 'h_200';
                            $width = 'w_200';
                        }
                        $updatePhoneNumber['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                    }else{
                        $updatePhoneNumber['image_url'] = base_url('assets/admin/no_image_avail.png');
                    }
                    if(!empty($updatePhoneNumber['app_language'])){
                        $updatePhoneNumber['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
                    }else{
                        $updatePhoneNumber['app_language_name'] = NULL;
                    }
                    $updatePhoneNumber['habits'] = $this->Api_model->getUserHabits($updatePhoneNumber['id']);
                    $i = 0;
                    foreach ($updatePhoneNumber['habits'] as $key => $value) {
                        if(!empty($value['image_url'])){
                            $explode = explode('upload', $value['image_url']);
                            if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                $height = 'h_'.$this->input->get('height');
                                $width = 'w_'.$this->input->get('width');
                            }else{
                                $height = 'h_200';
                                $width = 'w_200';
                            }
                            $updatePhoneNumber['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                        }else{
                            $updatePhoneNumber['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                        }
                        $i++;
                    }
                    $updatePhoneNumber['hobbies'] = $this->Api_model->getUserHobbies($updatePhoneNumber['id']);
                    $updatePhoneNumber['user_images'] = $this->Api_model->getUserImages($updatePhoneNumber['id']);
                    $i = 0;
                    foreach ($updatePhoneNumber['user_images'] as $key => $value) {
                        if(!empty($value['image_url'])){
                            $explode = explode('upload', $value['image_url']);
                            if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                                $height = 'h_'.$this->input->get('height');
                                $width = 'w_'.$this->input->get('width');
                            }else{
                                $height = 'h_200';
                                $width = 'w_200';
                            }
                            $updatePhoneNumber['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                        }else{
                            $updatePhoneNumber['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                        }
                        $i++;
                    }
                    $updatePhoneNumber['user_languages'] = $this->Api_model->getUserLanguages($updatePhoneNumber['id']);
                    $this->output->set_output(json_encode(['result' => 1, 'msg' =>'OTP Verified Successfully.' , 'data' => $updatePhoneNumber]));
                }else{
                    $this->output->set_output(json_encode(['result' => -1, 'msg' =>'OTP Mismatched.' , 'data' => 'OTP Mismatched.']));
                }
            }else{
                $this->output->set_output(json_encode(['result' => -1, 'msg' =>'User Not Found.' , 'data' => 'User Not Found.']));
            }
            return FALSE;
        }
    }
    /*Update Phone Number API Ends*/

    /*Delete And Disable Account API Starts */
    public function deleteDisableAccount(){
        $this->output->set_content_type("application/json");
        $token = $this->input->get_request_header('token');
        $key = $this->input->post('key');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        if($key === 'delete'){
            $deleteaccount = $this->Api_model->deleteUserAccount($token);
            $insertDeleteReason = $this->Api_model->insertDeleteReason($user_data['id'], $this->input->post('delete_reason'));
            if($insertDeleteReason){
                $this->output->set_output(json_encode(['result' => 1, 'msg' =>'Account Deleted.' , 'data' => NULL]));
                return false;
            }
        }else if($key === 'disable'){
            $disableaccount = $this->Api_model->disableUserAccount($token);
            if($disableaccount){
                $this->output->set_output(json_encode(['result' => 1, 'msg' =>'Account Disabled.' , 'data' => NULL]));
                return false;
            }
        }
    }
    /*Delete And Disable Account API Ends */

    /*Get User Data Api Starts*/
    public function getUserData(){
        $this->output->set_content_type("application/json");
        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);

        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $result = $this->Api_model->getUserData($token);
        if(!empty($result['specify_religion'])){
            $result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
        }else{
            $result['religion_specification'] = NULL;
        }
        if(!empty($result['image_url'])){
            $explode_image = explode('upload', $result['image_url']);
            if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                $height = 'h_'.$this->input->get('height');
                $width = 'w_'.$this->input->get('width');
            }else{
                $height = 'h_200';
                $width = 'w_200';
            }
            $result['image_url'] = $explode_image[0].'upload/'.$height.','.$width.$explode_image[1];
        }else{
            $result['image_url'] = base_url('assets/admin/no_image_avail.png');
        }
        if(!empty($result['app_language'])){
            $result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
        }else{
            $result['app_language_name'] = NULL;
        }
        $result['habits'] = $this->Api_model->getUserHabits($result['id']);
        $i = 0;
        foreach ($result['habits'] as $key => $value) {
            if(!empty($value['image_url'])){
                $explode = explode('upload', $value['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
            }
            $i++;
        }
        $result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
        $result['user_images'] = [];
        $user_images = $this->Api_model->getUserImages($result['id']);
        $i = 0;
		if(!empty($explode_image)){
        	array_unshift($result['user_images'], ["id"=>-1, 'image_url'=> $explode_image[0].'upload/'.$height.','.$width.$explode_image[1]]);
		}
        foreach ($user_images as $key => $value) {
            if(!empty($value['image_url'])){
                $explode = explode('upload', $value['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                array_push($result['user_images'], ["id" => $value['id'], 'image_url' => $value['image_url']]);
            }else{
                $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
            }
            $i++;
        }
        $result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
        if($result){
            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'User Data Found.' , 'data' => $result]));
        }else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'No Data Found.' , 'data' => NULL]));
        }
        return false;
    }
    /*Get User Data Api Ends*/

    /*Update User Image API starts*/
    public function updateUserImages(){
        $this->output->set_content_type("application/json");
        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
		//$image_id = $this->input->post('image_id');
        $device_type=$this->input->post('device_type');
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
		
        if (!empty($_FILES['image_url']['name'])) {
            $profile_image = $this->doUploadImage($_FILES['image_url']['tmp_name']);
        } else {
            $user = $this->Api_model->getUserById($user_data['id']);
            $profile_image = $user['image_url'];
        }

        if (!empty($_FILES['user_images']['name'][0])) {
            $user_images = $this->upload_files($_FILES['user_images']['tmp_name']);
        }
        
        if (!empty($user_images)) {
            if($device_type != 'android'){
                 $this->Api_model->deleteUserImages(NULL, $user_data['id']);
            }
            foreach ($user_images as $row) {
                $this->Api_model->insertUserImages($row, $user_data['id']);
            }
        }
        $result = $this->Api_model->updateUserImage($profile_image, $token);
        if($result){
            if(!empty($result['specify_religion'])){
                $result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
            }else{
                $result['religion_specification'] = NULL;
            }
            if(!empty($result['image_url'])){
                $explode = explode('upload', $result['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            if(!empty($result['app_language'])){
                $result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
            }else{
                $result['app_language_name'] = NULL;
            }
            $result['habits'] = $this->Api_model->getUserHabits($result['id']);
            $i = 0;
            foreach ($result['habits'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
            $result['user_images'] = $this->Api_model->getUserImages($result['id']);
            $i = 0;
            foreach ($result['user_images'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'Updated Successfully.' , 'data' => $result]));
        }else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Something went wrong.' , 'data' => NULL]));
        }
        return false;
    }
    /*Update User Image API ends*/

    /*Update User Profile API starts*/
    public function updateUserProfile(){
        $this->output->set_content_type("application/json");
        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        if(!empty($this->input->post('languages'))){
			$keytorep = ['"', "'", ' '];
            $keyrep = ['', '',''];
            $de = str_replace($keytorep, $keyrep, explode(',', trim($this->input->post('languages'), '[]')));
			
            $languageName = $this->Api_model->getLanguagesName($de);
            $removelanguages = $this->Api_model->removeLanguages($user_data['id']);
            foreach ($languageName as $key => $value) {
                $insertlanguages = $this->Api_model->insertLanguages($user_data['id'], $value['id'], $value['name']);
            }
        }
        if(!empty($this->input->post('habits'))){
			$keytorep = ['"', "'", ' '];
            $keyrep = ['', '',''];
            $de = str_replace($keytorep, $keyrep, explode(',', trim($this->input->post('habits'), '[]')));
			
            $deletehabits = $this->Api_model->deleteHabits($user_data['id']);
            $insertHabits = $this->Api_model->insertHabits($user_data['id'], $de);
        }
        if(!empty($this->input->post('hobbies'))){
			$keytorep = ['"', "'", ' '];
            $keyrep = ['', '',''];
            $de = str_replace($keytorep, $keyrep, explode(',', trim($this->input->post('hobbies'), '[]')));
			
            $deleteHobbies = $this->Api_model->deleteHobbies($user_data['id']);
            $insertHobbies = $this->Api_model->insertHobbies($user_data['id'], $de);
        }
        $result = $this->Api_model->updateUserData($token);
        if($result){
            if(!empty($result['specify_religion'])){
                    $result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
                }else{
                    $result['religion_specification'] = NULL;
                }
                if(!empty($result['image_url'])){
                    $explode = explode('upload', $result['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['image_url'] = base_url('assets/admin/no_image_avail.png');
                }
                if(!empty($result['app_language'])){
                    $result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
                }else{
                    $result['app_language_name'] = NULL;
                }
                $result['habits'] = $this->Api_model->getUserHabits($result['id']);
                $i = 0;
                foreach ($result['habits'] as $key => $value) {
                    if(!empty($value['image_url'])){
                        $explode = explode('upload', $value['image_url']);
                        if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                            $height = 'h_'.$this->input->get('height');
                            $width = 'w_'.$this->input->get('width');
                        }else{
                            $height = 'h_200';
                            $width = 'w_200';
                        }
                        $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                    }else{
                        $result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                    }
                    $i++;
                }
                $result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
                $result['user_images'] = $this->Api_model->getUserImages($result['id']);
                $i = 0;
                foreach ($result['user_images'] as $key => $value) {
                    if(!empty($value['image_url'])){
                        $explode = explode('upload', $value['image_url']);
                        if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                            $height = 'h_'.$this->input->get('height');
                            $width = 'w_'.$this->input->get('width');
                        }else{
                            $height = 'h_200';
                            $width = 'w_200';
                        }
                        $result['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                    }else{
                        $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                    }
                    $i++;
                }
                $result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'Updated Successfully.' , 'data' => $result]));
        }else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Something went wrong.' , 'data' => NULL]));
        }
        return false;
    }
    /*Update User Profile API ends*/

    /*Contact Us Api Start*/
    public function contactUs(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $result=$this->Api_model->contactUs($user_data['id']);
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Enquiry Submitted Successfully.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => NULL]));
        }
        return FALSE;
    }
    /*Contact Us Api End*/

    /*Update Firebase Token Starts*/
    public function updateFirebaseToken($value=''){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $result= $this->Api_model->updateFBToken($token, $this->input->post('firebase_token'));
        if ($result) {
            if(!empty($result['specify_religion'])){
                $result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
            }else{
                $result['religion_specification'] = NULL;
            }
            if(!empty($result['image_url'])){
                $explode = explode('upload', $result['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            if(!empty($result['app_language'])){
                $result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
            }else{
                $result['app_language_name'] = NULL;
            }
            $result['habits'] = $this->Api_model->getUserHabits($result['id']);
            $i = 0;
            foreach ($result['habits'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
            $result['user_images'] = $this->Api_model->getUserImages($result['id']);
            $i = 0;
            foreach ($result['user_images'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Token Updated Successfully.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => NULL]));
        }
        return FALSE;
    }
    /*Update Firebase Token Ends*/

    /*Logout API Starts*/
    public function logout(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $updateTokenAndFBToken = $this->Api_model->updateTokenAndFBToken($token);
        if ($updateTokenAndFBToken) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'User Logged Out Successfully.', 'data' => 'User Logged Out Successfully.']));
        } else {
            $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => NULL]));
        }
        return FALSE;
    }
    /*Logout API Ends*/

    /*Like Profile API Starts*/
    public function likeUnlikeProfile(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $visitor_id = $this->input->post('visitor_id');
		$status = $this->input->post('status');
        if($status == 'liked'){
            $checkLikeAvailability = $this->Api_model->checkLikeAvailability($visitor_id, $user_data['id'], 'liked');
            if(empty($checkLikeAvailability)){
                $checkUserLikeAvailability = $this->Api_model->checkLikeAvailability($user_data['id'], $visitor_id, 'liked');
                if(!empty($checkUserLikeAvailability)){
                    $checkChatUser = $this->Api_model->checkChatUser($visitor_id, $user_data['id']);
                    if(empty($checkChatUser)){
                        $result = $this->Api_model->insertChatUser($visitor_id, $user_data['id']);
                    }
                    if(empty($checkChatUser)){
                        $chat_id = $result;
                    }else{
                        $chat_id = $checkChatUser['id'];
                    }
                    $result = $this->Api_model->insertMessage($chat_id, $visitor_id, $user_data['id'], null, null);
                }
                $checkLikeAvailability = $this->Api_model->checkLikeAvailability($visitor_id, $user_data['id'], 'unliked');
                if(!empty($checkLikeAvailability)){
                    $result = $this->Api_model->updateLikeUserProfile($visitor_id, $user_data['id']);
                }else{
                    $result = $this->Api_model->likeUserProfile($visitor_id, $user_data['id']);
                }
                if ($result) {
                    $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Profile Liked Successfully.', 'data' => 'Profile Liked Successfully.', 'liked' => 1]));
                } else {
                    $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => NULL]));
                }
            }else{
                $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Profile Liked Successfully.', 'data' => 'Profile Liked Successfully.', 'liked' => 1]));
            }
        }else{
            $checkLikeAvailability = $this->Api_model->checkLikeAvailability($visitor_id, $user_data['id'], 'liked');
            $checkunLikeAvailability = $this->Api_model->checkLikeAvailability($visitor_id, $user_data['id'], 'unliked');
            if(!empty($checkLikeAvailability)){
                $result = $this->Api_model->unlikeUserProfile($visitor_id, $user_data['id']);
            }else if(!empty($checkunLikeAvailability)){
                $result = $this->Api_model->unlikeUserProfile($visitor_id, $user_data['id']);
            }else{
                $result = $this->Api_model->insertUnlikeUserProfile($visitor_id, $user_data['id']);
            }
            if ($result) {
                $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Profile Unliked.', 'data' => 'Profile Unliked.', 'liked' => 0]));
            } else {
                $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => NULL]));
            }
        }
        return FALSE;
    }
    /*Like Profile API End*/

    /*Profile Detail API Starts*/
    public function profileDetail(){
        $this->output->set_content_type('application/json');
        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        $visitor_id = $this->input->post('visitor_id');
        $checkVisitAvailability = $this->Api_model->checkLikeAvailability($visitor_id, $user_data['id'], 'visited');
        if(empty($checkVisitAvailability)){
            $visitProfile = $this->Api_model->visitProfile($visitor_id, $user_data['id']);
        }
        $result = $this->Api_model->getProfileDetail($visitor_id);
        if(!empty($result['image_url'])){
            $explode_image = explode('upload', $result['image_url']);
            if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                $height = 'h_'.$this->input->get('height');
                $width = 'w_'.$this->input->get('width');
            }else{
                $height = 'h_200';
                $width = 'w_200';
            }
            $result['image_url'] = $explode_image[0].'upload/'.$height.','.$width.$explode_image[1];
        }else{
            $result['image_url'] = base_url('assets/admin/no_image_avail.png');
        }
        if($result['religion'] == 'islam'){
            $result['religion'] = "ISLAM";
        }else if($result['religion'] == 'hinduism'){
            $result['religion'] = 'HINDUISM';
        }else{
            $result['religion'] = 'SIKHISM';
        }
        $getLikedProfile = $this->Api_model->checkLikeAvailability($visitor_id, $user_data['id'], 'liked');
        if(!empty($getLikedProfile)){
            $result['liked'] = '1';
        }else{
            $result['liked'] = '0';
        }
        $result['user_images'] = [];
        $user_images = $this->Api_model->getUserImages($visitor_id);
        if(!empty($explode_image)){
            array_unshift($result['user_images'], ["id"=>-1, 'image_url'=> $explode_image[0].'upload/'.$height.','.$width.$explode_image[1]]);
        }
        foreach ($user_images as $key => $value) {
            if(!empty($value['image_url'])){
                $explode = explode('upload', $value['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                array_push($result['user_images'], ["id" => $value['id'], 'image_url' => $value['image_url']]);
            }else{
                $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
            }
            $i++;
        }
        $data = [];
        $hobbies = $this->Api_model->getUserHobbiesName($user_data['id']);
        $j = 0;
        foreach ($hobbies as $key => $value) {
            $data[$j] = strtolower(str_replace(' ', '', $value['hobbies_name']));
            $j++;
        }
        $result['hobbies'] = $this->Api_model->getUserHobbies($visitor_id);
        $k = 0;
        foreach ($result['hobbies'] as $key => $value) {
            if(in_array(strtolower(str_replace(' ', '', $value['name'])), $data)){
                $result['hobbies'][$k]['mutual_interest'] = '1';
            }else{
                $result['hobbies'][$k]['mutual_interest'] = '0';
            }
            $k++;
        }
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => -1, 'msg' => 'No Data Found.', 'data' => NULL]));
        }
        return FALSE;
    }
    /*Profile Detail API Ends*/

    /*Activity List API Starts*/
    public function activityList(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $key = $this->input->post('key');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        if($key === 'nassibo_you'){
            $result = $this->Api_model->getNassiboYouList($user_data['id'], 'liked');
        }else if($key === 'visited_you'){
            $result = $this->Api_model->getNassiboYouList($user_data['id'], 'visited');
        }else if($key === 'you_nassibo'){
            $result = $this->Api_model->getYouNassiboList($user_data['id'], 'liked');
        }else if($key === 'you_visited'){
            $result = $this->Api_model->getYouNassiboList($user_data['id'], 'visited');
        }
        $i = 0;
        foreach ($result as $key => $value) {
            if(!empty($value['image_url'])){
                $explode = explode('upload', $value['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result[$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result[$i]['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            if(date('Y-m-d') == date('Y-m-d', strtotime($value['created_at']))){
                $date = 'Today';
            }else if(date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d')))) == date('Y-m-d', strtotime($value['created_at']))){
                $date = 'Yesterday';
            }else{
                $date = date('d M y', strtotime($value['created_at']));
            }
            $time = date("H:i A", strtotime($value['created_at']));
            $result[$i]['datetime'] = $time.' | '.$date;
            $getLikedProfile = $this->Api_model->checkLikeAvailability($value['id'], $user_data['id'], 'liked');
            if(!empty($getLikedProfile)){
                $result[$i]['liked'] = '1';
            }else{
                $result[$i]['liked'] = '0';
            }
            $i++;
        }
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
        }
        return FALSE;
        }
        
    /*Activity List API Ends*/

    /*Update Age Range and Distance Range API Starts*/
    public function updateAgeDistanceRange(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        $result = $this->Api_model->updateAgeDistanceRange($token, $user_data['age_range'], $user_data['distance_range'], $user_data['distance_unit'], $user_data['global'], $user_data['interested_in']);
        if($result){
            if(!empty($result['specify_religion'])){
                $result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
            }else{
                $result['religion_specification'] = NULL;
            }
            if(!empty($result['image_url'])){
                $explode = explode('upload', $result['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            if(!empty($result['app_language'])){
                $result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
            }else{
                $result['app_language_name'] = NULL;
            }
            $result['habits'] = $this->Api_model->getUserHabits($result['id']);
            $i = 0;
            foreach ($result['habits'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
            $result['user_images'] = $this->Api_model->getUserImages($result['id']);
            $i = 0;
            foreach ($result['user_images'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
            $this->output->set_output(json_encode(['result' => 1, 'msg' => "Data Updated Successfully.", 'data' => $result]));
        }else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' => "Something went wrong.", 'data' => NULL]));
        }
        return false;
    }
    /*Update Age Range and Distance Range API Ends*/

    /*Update App Langugae API Starts*/
    public function updateAppLanguage(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
		$language_name = $this->Api_model->getLanguageNameByCode($this->input->post('app_language'));
		if(!empty($language_name)){
			if($user_data['app_language'] == $this->input->post('app_language')){
				$this->output->set_output(json_encode(['result' => -1, 'msg' => $language_name['name']." Language is already selected.", 'data' => NULL]));
				return false;
			}
			$result = $this->Api_model->updateAppLanguage($token);
			if($result){
				if(!empty($result['specify_religion'])){
					$result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
				}else{
					$result['religion_specification'] = NULL;
				}
				if(!empty($result['image_url'])){
					$explode = explode('upload', $result['image_url']);
					if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
						$height = 'h_'.$this->input->get('height');
						$width = 'w_'.$this->input->get('width');
					}else{
						$height = 'h_200';
						$width = 'w_200';
					}
					$result['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
				}else{
					$result['image_url'] = base_url('assets/admin/no_image_avail.png');
				}
				if(!empty($result['app_language'])){
					$result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
				}else{
					$result['app_language_name'] = NULL;
				}
				$result['habits'] = $this->Api_model->getUserHabits($result['id']);
				$i = 0;
				foreach ($result['habits'] as $key => $value) {
					if(!empty($value['image_url'])){
						$explode = explode('upload', $value['image_url']);
						if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
							$height = 'h_'.$this->input->get('height');
							$width = 'w_'.$this->input->get('width');
						}else{
							$height = 'h_200';
							$width = 'w_200';
						}
						$result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
					}else{
						$result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
					}
					$i++;
				}
				$result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
				$result['user_images'] = $this->Api_model->getUserImages($result['id']);
				$i = 0;
				foreach ($result['user_images'] as $key => $value) {
					if(!empty($value['image_url'])){
						$explode = explode('upload', $value['image_url']);
						if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
							$height = 'h_'.$this->input->get('height');
							$width = 'w_'.$this->input->get('width');
						}else{
							$height = 'h_200';
							$width = 'w_200';
						}
						$result['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
					}else{
						$result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
					}
					$i++;
				}
				$result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
				$this->output->set_output(json_encode(['result' => 1, 'msg' => "Language Updated Successfully.", 'data' => $result]));
			}else{
				$this->output->set_output(json_encode(['result' => -1, 'msg' => "Something went wrong.", 'data' => NULL]));
			}
		}else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' => "Language not found.", 'data' => NULL]));
        }
        return false;
    }
    /*Update App Langugae API Ends*/

    /*Nassibo List API Starts*/
    public function nassiboUsersList(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);

        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $age_range = $this->input->post('age_range');
        $location_range = $this->input->post('location_range');
		
		$keytorep = ['"', "'", ' '];
        $keyrep = ['', '',''];
        $de = str_replace($keytorep, $keyrep, explode(',', trim($this->input->post('religion'), '[]')));
        $religion = $de;
		
		$keytoreprel = ['"', "'", ' '];
        $keyreprel = ['', '',''];
        $specify = str_replace($keytoreprel, $keyreprel, explode(',', trim($this->input->post('religion_specify'), '[]')));
        $religion_specify = $specify;
		
		$keytorephab = ['"', "'", ' '];
        $keyrephab = ['', '',''];
        $habits_filter = str_replace($keytorephab, $keyrephab, explode(',', trim($this->input->post('habits'), '[]')));
        $habits = $habits_filter;
		
		$keytoreplan = ['"', "'", ' '];
        $keyreplan = ['', '',''];
        $language_filter = str_replace($keytoreplan, $keyreplan, explode(',', trim($this->input->post('language'), '[]')));
        $language = $language_filter;
		
        $user_hobbies_list = [];
        $user_hobbies = $this->Api_model->getUserHobbiesName($user_data['id']);
        $j = 0;
        foreach ($user_hobbies as $key4 => $value4) {
            $user_hobbies_list[$j] = $value4['hobbies_name'];
            $j++;
        }
		$likedUsers = [];
		//$unlikedUsers = [];
		$likedUsers = $this->Api_model->getLikedUsersList($user_data['id'], 'liked');
       
		//$unlikedUsers = $this->Api_model->getUnlikedUsersList($user_data['id'], 'unliked');
		//$totalLikedUnlikedUsers = array_merge($likedUsers, $unlikedUsers);
        $b=0;
        $liked_user_ids = [];
        if(!empty($likedUsers)){
            foreach($likedUsers as $likedUser){
                $liked_user_ids[$b] = $likedUser['visitor_id'];
                $b++;
            }
        }else{
            $liked_user_ids = [];
        }
        $results = $this->Api_model->getNassiboUsersList($token, $user_data['latitude'], $user_data['longitude'], $user_data['age_range_status'], $user_data['age_range'], $user_data['interested_in'], $age_range, $location_range, $religion, $religion_specify, $habits, $language, $user_hobbies_list, $liked_user_ids, $user_data['id']);
        $data = [];
        if(!empty($results)){
            $i = 0;
            foreach ($results as $key => $value) {
                $results[$i]['distance'] = user_distance($user_data['latitude'], $user_data['longitude'], $value['latitude'], $value['longitude']);
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $results[$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $results[$i]['image_url'] = base_url('assets/admin/no_image_avail.png');
                }
                $i++;
            }
        }
        $result = [];
        if(!empty($results)){
            foreach ($results as $key => $final_result) {
                if(!empty($location_range)){
                    if($user_data['distance_unit'] != 'mi'){
                        if($final_result['distance'] <= $location_range){
                            array_push($result, $final_result);
                        }
                    }else{
                        if(($final_result['distance']/1.609) <= $location_range){
                            array_push($result, $final_result);
                        }
                    }
                }elseif(!empty($user_data['distance_range'])){
                    if($user_data['distance_unit'] != 'mi'){
                        if($final_result['distance'] <= $user_data['distance_range']){
                            array_push($result, $final_result);
                        }
                    }else{
                        if(($final_result['distance']/1.609) <= $user_data['distance_range']){
                            array_push($result, $final_result);
                        }
                    }
                }else{
                    array_push($result, $final_result);
                }
            }
        }
        $hobbies = $this->Api_model->getUserHobbiesName($user_data['id']);
        $j = 0;
        foreach ($hobbies as $key3 => $value3) {
            $data[$j] = strtolower(str_replace(' ', '', $value3['hobbies_name']));
            $j++;
        }

        if(!empty($result)){
            $k = 0;
            foreach ($result as $key1 => $value1) {
                $getLikedProfile = $this->Api_model->checkLikeAvailability($value1['id'], $user_data['id'], 'liked');
                if(!empty($getLikedProfile)){
                    $result[$k]['liked'] = '1';
                }else{
                    $result[$k]['liked'] = '0';
                }
                $result[$k]['hobbies'] = $this->Api_model->getUserHobbies($value1['id']);
                $k++;
            }

            foreach ($result as $key2 => $value2) {
                foreach($value2['hobbies'] as $key3 => $value3){
                    if(in_array(strtolower(str_replace(' ', '', $value3['name'])), $data)){
                        $result[$key2]['hobbies'][$key3]['mutual_interest'] = '1';
                    }else{
                        $result[$key2]['hobbies'][$key3]['mutual_interest'] = '0';
                    }
                }
            }
        }
        
        if($result){
            $this->output->set_output(json_encode(['result' => 1, 'msg' => "Data Found.", 'data' => $result]));
        }else{
            $this->output->set_output(json_encode(['result' => 1, 'msg' => "No Data Found.", 'data' => NULL]));
        }
        return false;
    }
    /*Nassibo List API Ends*/

    /*Report User API Starts*/
    public function reportUser(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);

        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $visitor_id = $this->input->post('visitor_id');
        $result = $this->Api_model->reportUser($user_data['id'], $visitor_id);

        $visitorReportCount = $this->Api_model->visitorReportCount($visitor_id);
        if($visitorReportCount >= 20){
            $sendNotification = $this->Api_model->sendReportUserNotification($visitor_id);
        }
        if($result){
            $this->output->set_output(json_encode(['result' => 1, 'msg' => "User Reported Successfully.", 'data' => 'User Reported Successfully.']));
        }else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' => "No Data Found.", 'data' => NULL]));
        }
        return false;
    }
    /*Report User API Ends*/
	
	/*Block User API Starts*/
    public function blockUser(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);

        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $visitor_id = $this->input->post('visitor_id');
        $checkBlockUser = $this->Api_model->checkBlockUser($visitor_id, $user_data['id']);
        if(empty($checkBlockUser)){
            $result = $this->Api_model->blockUserProfile($visitor_id, $user_data['id']);
            if ($result) {
                $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Profile Blocked.', 'data' => 'Profile Blocked.']));
            } else {
                $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => NULL]));
            }
        }else{
            $result = $this->Api_model->unblockUserProfile($visitor_id, $user_data['id']);
            if ($result) {
                $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Profile Unblocked.', 'data' => 'Profile Unblocked.']));
            } else {
                $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => NULL]));
            }
        }
        return false;
    }
    /*Block User API Ends*/

    /*Send Message API Starts*/
    public function sendMessage(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $visitor_id = $this->input->post('visitor_id');
        $message = $this->input->post('message');
        $blockedUser = $this->Api_model->checkBlockedUserMessages($visitor_id, $user_data['id']);
        if(!empty($blockedUser)){
            if($user_data['id'] == $blockedUser['user_id']){
                $this->output->set_output(json_encode(['result' => -1, 'msg' => 'User is blocked.', 'data' => NULL]));
            }else{
                $this->output->set_output(json_encode(['result' => -1, 'msg' => 'You are blocked.', 'data' => NULL]));
            }
            return FALSE;
        }
        $checkChatUser = $this->Api_model->checkChatUser($visitor_id, $user_data['id']);
        if (!empty($_FILES['media']['name'][0])) {
            $media_url = $this->upload_files($_FILES['media']['tmp_name']);
        }else{
            $media_url = NULL;
        }

        if(empty($checkChatUser)){
            $result = $this->Api_model->insertChatUser($visitor_id, $user_data['id']);
        }
        if(empty($checkChatUser)){
            $chat_id = $result;
        }else{
            $chat_id = $checkChatUser['id'];
        }
        $result = $this->Api_model->insertMessage($chat_id, $visitor_id, $user_data['id'], $message, $media_url);
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Message Sent Successfully.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something went wrong.', 'data' => NULL]));
        }
        return false;
    }
    /*Send Message API Ends*/

    /*Chat List API Starts*/
    public function getChatList(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $chat_detail = $this->Api_model->checkChat($user_data['id']);
        if(!empty($chat_detail)){
            $i = 0;
            foreach($chat_detail as $key => $value){
                $chat_messages[$i] = $this->Api_model->getChatMessages($value['id']);
                $i++;
            }
            $j = 0;
            foreach($chat_messages as $key1 => $value1){
                if($user_data['id'] == $value1['sender_id']){
                    $user_id = $value1['reciever_id'];
                }else{
                    $user_id = $value1['sender_id'];
                }
                $user_data_messages = $this->Api_model->getUserDataMessages($user_id);
				if($user_data['id'] == $value1['sender_id']){
                    $chat_messages[$j]['sender_id'] = $value1['sender_id'];
                }else{
                    $chat_messages[$j]['sender_id'] = $value1['reciever_id'];
                }
                if($user_data['id'] == $value1['sender_id']){
                    $chat_messages[$j]['reciever_id'] = $value1['reciever_id'];
                }else{
                    $chat_messages[$j]['reciever_id'] = $value1['sender_id'];
                }
                $chat_messages[$j]['time'] = date('h:i A', strtotime($value1['created_at']));
                $chat_messages[$j]['first_name'] = $user_data_messages['first_name'];
                $chat_messages[$j]['last_name'] = $user_data_messages['last_name'];
                $chat_messages[$j]['online_status'] = $user_data_messages['online_status'];
                if(!empty($user_data_messages['image_url'])){
                    $explode = explode('upload', $user_data_messages['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $chat_messages[$j]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $chat_messages[$j]['image_url'] = base_url('assets/admin/no_image_avail.png');
                }
                $j++;
            }
        }
        if($chat_messages){
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found', 'data'=>$chat_messages]));
        }else{
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Message Found.', 'data'=>$chat_messages]));
        }
    }
    /*Chat List API Ends*/
    
    public function getChatListV2(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }

        $chat_detail = $this->Api_model->checkChat($user_data['id']);
     
        $i=0;
        $result=[];
        if(!empty($chat_detail)){
            $i=0;
            foreach($chat_detail as $row){
                $user_id=$this->checkUserStatus($row['user_id']);
                $reciever_id=$this->checkUserStatus($row['reciever_id']);
                if($user_id && $reciever_id){
                    $result[$i]=$row;
                    $i++;
                }
            }
        }
        $chat_detail = $result;
        if(!empty($chat_detail)){
            $i = 0;
            foreach($chat_detail as $key => $value){
                $chat_messages[$i] = $this->Api_model->getChatMessages($value['id']);
                $i++;
            }
            $j = 0;
            foreach($chat_messages as $key1 => $value1){
                if($user_data['id'] == $value1['sender_id']){
                    $user_id = $value1['reciever_id'];
                }else{
                    $user_id = $value1['sender_id'];
                }
                $user_data_messages = $this->Api_model->getUserDataMessages($user_id);
				if($user_data['id'] == $value1['sender_id']){
                    $chat_messages[$j]['sender_id'] = $value1['sender_id'];
                }else{
                    $chat_messages[$j]['sender_id'] = $value1['reciever_id'];
                }
                if($user_data['id'] == $value1['sender_id']){
                    $chat_messages[$j]['reciever_id'] = $value1['reciever_id'];
                }else{
                    $chat_messages[$j]['reciever_id'] = $value1['sender_id'];
                }
                @$chat_messages[$j]['time'] = date('h:i A', strtotime($value1['created_at']));
                @$chat_messages[$j]['first_name'] = $user_data_messages['first_name'];
                @$chat_messages[$j]['last_name'] = $user_data_messages['last_name'];
                @$chat_messages[$j]['online_status'] = $user_data_messages['online_status'];

                $blockedUser = $this->Api_model->checkBlockedUserMessages($value1['reciever_id'], $user_data['id']);
                dd($blockedUser) ;
                if(!empty($blockedUser)){
                    if($user_data['id'] == $blockedUser['user_id']){
                        @$chat_messages[$j]['is_blocked'] = 'yes';
                    }else{
                        @$chat_messages[$j]['is_blocked'] = 'yes';
                    }
                }else{
                    @$chat_messages[$j]['is_blocked'] = 'no';
                }
               
                if(!empty($user_data_messages['image_url'])){
                    $explode = explode('upload', $user_data_messages['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $chat_messages[$j]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $chat_messages[$j]['image_url'] = base_url('assets/admin/no_image_avail.png');
                }
                $j++;
            }
        }
        if(!empty($chat_messages)){
            $finaldata=[];
            $today['days_name']='today';
            $yesterday['days_name']='yesterday';
            $tod=date('y-m-d');
            $yest=date('y-m-d',strtotime('-1 day'));
            $group=($this->group_by('date',$chat_messages));
    
            $finalarray=[]; 
            foreach($group as $key=>$row){
                if(date('Y-m-d',strtotime($key)) == date('Y-m-d',strtotime($tod))){
                    $key='Today';
                }
                if(date('Y-m-d',strtotime($key)) == date('Y-m-d',strtotime($yest))){
                    $key='Yesterday';
                }
                if(!($key == 'Today' || $key == 'Yesterday')){
                    $key=date('d/m/Y',strtotime($key));
                }
                $temp['days_name']=$key;
                $temp['user_list']=$row;
                $finalarray[]=$temp;
                
            }
           
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found', 'data'=>$finalarray]));
        }else{
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Message Found.', 'data'=>null]));
        }
    }
   
    public function checkUserStatus($user_id){
        $user_data=$this->Api_model->getUserStatus($user_id);
        //dd($user_data);
        if($user_data['status'] == 'Active'){
            return true;
        }else{
            return false;
        }
    }

    public function orderBy($data,$key){
        usort($data, function ($dt1, $dt2) use ($data) {
             return strtotime($data[$dt2]['created_at']) - strtotime($data[$dt1]['created_at']);
        });
    }
   
    public function group_by($key, $data) {
        $result = array();
    
        foreach($data as $val) {
            if(array_key_exists($key, $val)){
                $result[$val[$key]][] = $val;
            }else{
                $result[""][] = $val;
            }
        }
    
        return $result;
    }

	
	/*All Messages API Starts*/
    public function getAllMessages(){
        $this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        $chat_id = $this->input->post('chat_id');
        if(!empty($chat_id)){
            $chat_detail = $this->Api_model->checkChatData($chat_id);
            if($user_data['id'] != $chat_detail['user_id']){
                $blocked_user_id = $chat_detail['user_id'];
            }else{
                $blocked_user_id = $chat_detail['reciever_id'];
            }
            $blocked_user = $this->Api_model->checkBlockedUser($blocked_user_id);
            if(!empty($blocked_user)){
                $data['blocked_user'] = '1';
            }else{
                $data['blocked_user'] = '0';
            }
            $chat_user_detail = $this->Api_model->getChatUserDetail($chat_detail['user_id']);
            $chat_reciever_detail = $this->Api_model->getChatUserDetail($chat_detail['reciever_id']);
        
            if(!empty($chat_user_detail['image_url'])){
                $explode = explode('upload', $chat_user_detail['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $chat_user_detail['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $chat_user_detail['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            if(!empty($chat_reciever_detail['image_url'])){
                $explode = explode('upload', $chat_reciever_detail['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $chat_reciever_detail['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $chat_reciever_detail['image_url'] = base_url('assets/admin/no_image_avail.png');
            }

            if($chat_user_detail['id'] == $user_data['id']){
                $data['user_detail'] = $chat_reciever_detail;
            }else{
                $data['user_detail'] = $chat_user_detail;
            }
            $dates = [];
            $chat_dates = $this->Api_model->getAllChatDates($chat_id);
            //dd($chat_dates); die;
            foreach($chat_dates as $key => $date){
                $dates[$key]['date'] = $chat_dates[$key]['date'];
                $dates[$key]['chat_data'] = $this->Api_model->getAllChatMessages($date['date'], $chat_id);
            }
            $data['chat_messages'] = $dates;
            $i = 0;
            foreach($data['chat_messages'] as $key => $value){
                $j = 0;
                foreach($value['chat_data'] as $value1){
                    if($value1['sender_id'] == $user_data['id']){
                        $data['chat_messages'][$key]['chat_data'][$j]['user_id'] = $user_data['id'];
                    }else{
                        $data['chat_messages'][$key]['chat_data'][$j]['user_id'] = $value1['sender_id'];
                    }
                    $j++;
                }
                $i++;
            }
        }
        foreach($data['chat_messages'] as $key1 => $chat_msgs){
           
            foreach($chat_msgs['chat_data'] as $key => $chat_datas){
                if($chat_datas['attachment'] == 'no'){
                    $data['chat_messages'][$key1]['chat_data'][$key]['attached_files'] = NULL;
                    if($chat_datas['message'] == NULL || $chat_datas['message'] == ''){
                       // unset($data['chat_messages'][$key1]['chat_data'][$key]);
                    }
                }else{
                    $attachment = $this->Api_model->getAttachedFiles($chat_datas['id']);
                    $data['chat_messages'][$key1]['chat_data'][$key]['attached_files'] = $attachment;
                }
            }
        }
        foreach($data['chat_messages'] as $key1 => $chat_msgs){
             $final_array= [];
            foreach($chat_msgs['chat_data'] as $key => $chat_datas){
                if($chat_datas['message'] == NULL && $chat_datas['attachment'] == 'no'){
                    $data['chat_messages'][$key1]['chat_data'][$key]['attached_files'] = NULL;
                    if($chat_datas['message'] == NULL || $chat_datas['message'] == ''){
                        
                    }
                }else{
                    $attachment = $this->Api_model->getAttachedFiles($chat_datas['id']);
                    $data['chat_messages'][$key1]['chat_data'][$key]['attached_files'] = $attachment;
                    array_push($final_array, $chat_datas);
                }
            }
           
            $data['chat_messages'][$key1]['chat_data'] = $final_array;
        }
      
        foreach($data['chat_messages'] as $key1 => $chat_msgs){
            $i = 0;
            foreach($chat_msgs['chat_data'] as $key => $chat_datas){
                if($chat_datas['attachment'] == 'no'){
                    if($chat_datas['message'] == NULL || $chat_datas['message'] == ''){
                        unset($data['chat_messages'][$key1]['chat_data'][$key]);
                    }
                }
            }
        }
        if($data){
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Data Found', 'data'=>$data]));
        }else{
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Message Found.', 'data'=>$data]));
        }
    }
    /*All Messages API Ends*/
	
	public function userFullData($token){
        $this->output->set_content_type('application/json');
        $user_data = $this->Api_model->getUserIdByToken($token);
        
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        $result = $this->Api_model->getUserData($token);
        if($result){
            if(!empty($result['specify_religion'])){
                $result['religion_specification'] = $this->Api_model->getReligionSpecification($token)->religion_specification;
            }else{
                $result['religion_specification'] = NULL;
            }
            if(!empty($result['image_url'])){
                $explode = explode('upload', $result['image_url']);
                if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                    $height = 'h_'.$this->input->get('height');
                    $width = 'w_'.$this->input->get('width');
                }else{
                    $height = 'h_200';
                    $width = 'w_200';
                }
                $result['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
            }else{
                $result['image_url'] = base_url('assets/admin/no_image_avail.png');
            }
            if(!empty($result['app_language'])){
                $result['app_language_name'] = $this->Api_model->getUserAppLanguage($token)->language_name;
            }else{
                $result['app_language_name'] = NULL;
            }
            $result['habits'] = $this->Api_model->getUserHabits($result['id']);
            $i = 0;
            foreach ($result['habits'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['habits'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['habits'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['hobbies'] = $this->Api_model->getUserHobbies($result['id']);
            $result['user_images'] = $this->Api_model->getUserImages($result['id']);
            $i = 0;
            foreach ($result['user_images'] as $key => $value) {
                if(!empty($value['image_url'])){
                    $explode = explode('upload', $value['image_url']);
                    if(!empty($this->input->get('height')) && !empty($this->input->get('width'))){
                        $height = 'h_'.$this->input->get('height');
                        $width = 'w_'.$this->input->get('width');
                    }else{
                        $height = 'h_200';
                        $width = 'w_200';
                    }
                    $result['user_images'][$i]['image_url'] = $explode[0].'upload/'.$height.','.$width.$explode[1];
                }else{
                    $result['user_images'][$i]['image_url'] = base_url('admin/assets/no_image_avail.png');
                }
                $i++;
            }
            $result['user_languages'] = $this->Api_model->getUserLanguages($result['id']);
            return $result;
        }
    }
	
	public function updateUserOnlineStatus(){
        $this->output->set_content_type('application/json');
        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);

        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        $status = $this->input->post('status');
        $result = $this->Api_model->updateOnlineStatus($token, $status);
        if(!empty($result)){
            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'Status Updated.' , 'data' => 'Status Updated.']));
        }else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Someting went wrong.' , 'data' => NULL]));
        }
        return FALSE;
    }
	public function usersetLatLong(){
		$this->output->set_content_type('application/json');

        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
		if(!empty($user_data)){
			if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
	            header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
	            $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
	            return false;
			}
        }
		$user_id=$user_data['id'];

        $result = $this->Api_model->usersetLatLong($user_id);
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Lat Long Set.', 'data' => $result]));
        } else {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'No Data Found.', 'data' => NULL]));
            return FALSE;
        }
	}
    public function deleteImage(){
        $this->output->set_content_type("application/json");
        $token = $this->input->get_request_header('token');
        $user_data = $this->Api_model->getUserIdByToken($token);
        if(empty($user_data)){
            header('HTTP/1.1 402 User already logged in on a different device.', true, 402);
            $this->output->set_output(json_encode(['result' => -2, 'msg' => 'User already logged in on a different device.', 'data' => NULL]));
            return false;
        }
        if(!empty($user_data)){
            if($user_data['status'] == 'Inactive' || $user_data['status'] == 'Deleted' || $user_data['blocked'] == 'yes'){
                header("HTTP/1.1 402 User's account is Inactive, Blocked or Deleted.", true, 402);
                $this->output->set_output(json_encode(['result' => -2, 'msg' => "User's account is Inactive, Blocked or Deleted.", 'data' => NULL]));
                return false;
            }
        }
        $image_id=$this->input->post('image_id');
        $result= $this->Api_model->deleteUserImages($image_id, $user_data['id']);
        if($result){
            $results = $this->Api_model->getUserImages($user_data['id']);
            $this->output->set_output(json_encode(['result' => 1, 'msg' =>'Image Delted.' , 'data' => $results]));
        }else{
            $this->output->set_output(json_encode(['result' => -1, 'msg' =>'Something went wrong.' , 'data' => NULL]));
        }
        return false;
           
    }
}
?>