<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

	require_once("Rest.inc.php");
	class API extends REST {

		public $data = "";

		const DB_SERVER = "e2e-28-62.e2enetworks.net.in";
		const DB_USER = "root";
		const DB_PASSWORD = "rEuL5eFcLe91HdJw";
		const DB = "db_doctor";

		private $db = NULL;

		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		/*
		 *  Database connection
		*/
		private function dbConnect(){
			//$this->db = mysqli_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
			
			$this->db = new PDO('mysql:host=e2e-28-62.e2enetworks.net.in;dbname=db_doctor', 'root', 'rEuL5eFcLe91HdJw', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );
			/*if($this->db)
				mysqli_select_db($this->db,'db_doctor');*/
//print_r($this->db); exit();
		}
		/*
		 * Public method for access api.
		 * This method dynmically call the method based on the query string
		 *
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['value'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404);				// If the method not exist with in this class, response would be "Page not found".
		}
		public function imageValidation($fileName) {
				$allowedExtensions = array("jpg","png","jpeg");
				$image_name = uniqid();
				$uploads_dir='images';
				$tmp_name = $_FILES["fileUpload"]["tmp_name"];

				$ext = explode(".",strtolower(basename($fileName)));
				$ext = end($ext);
						if(in_array($ext,$allowedExtensions)) {
							move_uploaded_file($tmp_name, "$uploads_dir/$fileName");
						} else{
							$error = array('status' => "Sucess", "msg" => "jpg, png, jpeg are required");
							return $this->response($this->json($error), 200);
						}
				}

				function fetch_country(){
					$sql = "Select cntry_id, cntry_name FROM m_country";

					$stmt = $this->db->prepare($sql);
					$stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

							$error = array('status' => "Sucess", "msg" => "Country Results", "Data"=>$results);
							return $this->response($this->json($error), 200);
				}
				function fetch_city_state_wise() {
				
					if($this->get_request_method() != "POST"){
						$this->response('',406);
					}
                                  
					if(!isset($_POST['state_id'])) {
						$error = array('status' => "Failed", "msg" => "Parameter state_id is required");
						$this->response($this->json($error), 200);
					}
					
					$state_id = $_POST['state_id'];

					if($state_id || $state_id!=0) {
					
						$sql = "SELECT cty_id, cty_name, state_id FROM m_city WHERE state_id = ".$state_id." ";
						$stmt = $this->db->prepare($sql);
						$stmt->execute();
						$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					
						$error = array('status' => "Sucess", "msg" => "State Results", "Data"=>$results);
						return $this->response($this->json($error), 200);
					} else{
						$error = array('status' => "Sucess", "msg" => "Value must be filled and cannot be zero");
						return $this->response($this->json($error), 200);
					}
					
				}
				function fetch_state(){
					$sql = "Select st_id, st_name, country_id FROM m_state";

					$stmt = $this->db->prepare($sql);
					$stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

						$error = array('status' => "Sucess", "msg" => "State Results", "Data"=>$results);
						return $this->response($this->json($error), 200);
				}
				function fetch_city(){
					$sql = "Select cty_id, cty_name, state_id FROM m_city";
					$stmt = $this->db->prepare($sql);
					$stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

						$error = array('status' => "Sucess", "msg" => "State Results", "Data"=>$results);
						return $this->response($this->json($error), 200);
				}

			function createDateRangeArray($start, $end) {
					$start = date('Y-m-d', strtotime($start) );
					$end = date('Y-m-d', strtotime($end) );
				$range = array();
				if (is_string($start) === true) $start = strtotime($start);
				if (is_string($end) === true ) $end = strtotime($end);

				if ($start > $end) return createDateRangeArray($end, $start);
				do {
					$range[] = date('Y-m-d', $start);

					$start = strtotime("+ 1 day", $start);
				}
				while($start <= $end);
				return $range;
			}
// Check date in Range
	function check_in_range($start_date, $end_date, $date_from_user)
		{
		  // Convert to timestamp
		  $start_ts = strtotime($start_date);
		  $end_ts = strtotime($end_date);
		  $user_ts = strtotime($date_from_user);
		  // Check that user date is between start & end
		  return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
		}

		/*
		 *	Simple login API
		 *  Login must be POST method
		 *  email : <USER EMAIL>
		 *  pwd : <USER PASSWORD>
		 */
		function getToken($length) {
 		    $key = '';
 		    $keys = array_merge(range(0, 9), range('a', 'z'));
 		    for ($i = 0; $i < $length; $i++) {
 		        $key .= $keys[array_rand($keys)];
 		    }

 		    return $key;
 		}

		// Doctor Registeration
				public function doctor_registeration() {
					// Cross validation if the request method is POST else it will return "Not Acceptable" status
					if($this->get_request_method() != "POST"){
						$this->response('',406);
					}
					if(!isset($_POST['password']) && !isset($_POST['email']) && !isset($_POST['name']) && !isset($_POST['confirm_password']) && !isset($_POST['mobile_number']) ) {
						$error = array('status' => "Failed", "msg" => "Parameter name, email, mobile no,password and confirm_password are require");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['name'])) {
						$error = array('status' => "Failed", "msg" => "Parameter name is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['email'])) {
						$error = array('status' => "Failed", "msg" => "Parameter email is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['password'])) {
						$error = array('status' => "Failed", "msg" => "Parameter password is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['confirm_password'])) {
						$error = array('status' => "Failed", "msg" => "Parameter confirm_password is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['qualification'])) {
						$error = array('status' => "Failed", "msg" => "Parameter qualification is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['specialization'])) {
						$error = array('status' => "Failed", "msg" => "Parameter specialization is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['mobile_number'])) {
						$error = array('status' => "Failed", "msg" => "Parameter mobile_number is required");
						$this->response($this->json($error), 200);
					}

					$name = $this->_request['name'];
					$email = $this->_request['email'];
					$password = $this->_request['password'];
					$cpassword = $this->_request['confirm_password'];
					$phone_no = $this->_request['mobile_number'];
					$user_image = isset($_FILES["fileUpload"]["name"]) ? $_FILES["fileUpload"]["name"] : '';
					$address = isset($this->_request['address']) ? $this->_request['address'] : '' ;
					$city = isset($this->_request['city']) ? $this->_request['city'] : '';
					$district = isset($this->_request['district']) ? $this->_request['district'] : '';
					$state = isset($this->_request['state']) ? $this->_request['state'] : '';
					$pincode = isset($this->_request['pincode']) ? $this->_request['pincode'] : '';
					$qualification = isset($this->_request['qualification']) ? $this->_request['qualification'] : '';
					$specializaton = isset($this->_request['specialization']) ? $this->_request['specialization'] : '';

					$created_at = date('Y-m-d H:i:s');
					$updated_at = date('Y-m-d H:i:s');

					//Upload image
					if($user_image){
						$this->imageValidation($_FILES["fileUpload"]["name"]);
					}

					if($phone_no || $email) {
					$checkphoneExists = "SELECT du_id FROM `table_doctor` WHERE (`du_phone_no` = '".$phone_no."') OR (`du_email` = '".$email."')";
					$stmt = $this->db->prepare($checkphoneExists);
					$stmt->execute();
					$fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);

					if(count($fetchData)==0) {
							if(!isset($phone_no)) {
								$error = array('status' => "Failed", "msg" => "Mobile number is required");
								$this->response($this->json($error), 200);
							}
							if(strlen($phone_no)!='10'){
								$error = array('status' => "Failed", "msg" => "Mobile number should be of 10 digit");
								$this->response($this->json($error), 200);
							}
							if(!is_numeric($phone_no)){
								$error = array('status' => "Failed", "msg" => "Number are allowed only");
								$this->response($this->json($error), 200);
							}
							if($password!=$cpassword){
								$error = array('status' => "Failed", "msg" => "Password and Confirm password is not matched");
								$this->response($this->json($error), 200);
							}
							// Input validations
							if(!empty($name) and !empty($email) and !empty($password) and !empty($cpassword)) {

									$hashed_password = sha1($password);
									$token_value = 'Api'.$this->getToken(50);

									    $sql = "INSERT INTO table_doctor (`du_name`, `du_email`, `du_phone_no`, `du_pic`, `du_password`, `du_address`, `du_city`, `du_district`, `du_state`, `du_pincode`,`remember_token`, `specializaton`, `qualification`, `created_at`, `updated_at`)
											VALUES ('".$name."', '".$email."', ".$phone_no.", '".$user_image."', '".$hashed_password."', '".$address."', '".$city."', '".$district."', '".$state."', '".$pincode."','".$token_value."', '".$specializaton."', '".$qualification."', '".$created_at."', '".$updated_at."' )";
											$execute = $this->db->query($sql);
											//$temp = $this->db->lastInsertId(); //Fetch the last instered ID

											$error = array('status' => "Sucess", "msg" => "Doctor Successfully created!");
											$this->response($this->json($error), 200);
							} else{
							$error = array('status' => "Failed", "msg" => "Name, Email password and confirm_password required");
							$this->response($this->json($error), 200);
						}
					} else{
					$error = array('status' => "Failed", "msg" => "Email ID or Phone number already exists");
					$this->response($this->json($error), 200);
				}
			}
					// If invalid inputs "Bad Request" status message and reason
					$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
					$this->response($this->json($error), 200);
			}
			// End Registeration


// Login of Doctor and User
		private function doctor_login() {
			// Cross validation if the request method is POST else it will return "Not Acceptable" status
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			if(!isset($_POST['password']) && !isset($_POST['email'])) {
				$error = array('status' => "Failed", "msg" => "Parameter email and password is required");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['email'])) {
				$error = array('status' => "Failed", "msg" => "Parameter email is required");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['password'])) {
				$error = array('status' => "Failed", "msg" => "Parameter password is required");
				$this->response($this->json($error), 200);
			}
			$email = $this->_request['email'];
			$password = $this->_request['password'];
			$hashed_password = sha1($password);
			// Input validations

			if(!empty($email) and !empty($password)) {
				if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$sql = "SELECT du_id,du_name,du_email,du_phone_no,du_pic,du_address,remember_token FROM table_doctor WHERE du_email = '$email' AND du_password = '".$hashed_password."' LIMIT 1";
					$stmt = $this->db->prepare($sql);
					$stmt->execute();
				  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if($stmt->rowCount()=='0') {
              $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
              $this->response($this->json($error), 200);
            }
            				if($results[0]['du_pic']==''){
						$error = array('status' => "Success", "msg" => "Sucessfully Login!", "data" => $results );
					} else{
						$results[0]['du_pic']='images/'.$results[0]['du_pic'];
						$error = array('status' => "Success", "msg" => "Sucessfully Login!", "data" => $results );
					}
						$this->response($this->json($error), 200);
				}
			} else{
				$error = array('status' => "Failed", "msg" => "Fields are required");
				$this->response($this->json($error), 200);
			}
			// If invalid inputs "Bad Request" status message and reason
			$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			$this->response($this->json($error), 200);
		}
// End Login of Doctor and User

		// Profile Updation
		private function doctor_reset_password(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			if(!isset($_POST['password']) ) {
				$error = array('status' => "Failed", "msg" => "Parameter password is require");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['confirm_password']) ) {
				$error = array('status' => "Failed", "msg" => "Parameter confirm_password are require");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['doctor_id']) ) {
				$error = array('status' => "Failed", "msg" => "Parameter table_doctor_id are require");
				$this->response($this->json($error), 200);
			}

			$user_id = $this->_request['doctor_id'];
			$password = $this->_request['password'];
			$cpassword = $this->_request['confirm_password'];

			if( !empty($user_id) && !empty($password) && !empty($cpassword) ) {

				if($password!=$cpassword) {
					$error = array('status' => "Failed", "msg" => "Password and Confirm password is not matched");
					$this->response($this->json($error), 200);
				} else{
						$hashed_password = sha1($password);
						$sql = "UPDATE table_doctor SET du_password='".$hashed_password."' WHERE du_id='".$user_id."' ";
						$stmt = $this->db->prepare($sql);
						$update = $stmt->execute();
						//$fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);
						if(count($update)==1){
							$error = array('status' => "Success", "msg" => "Profile Updated");
							$this->response($this->json($error), 200);
						}
				}
			}
		}

		public function docotor_profile_updation() {
			$user_id = $_POST['table_doctor_id'];
			$sql = "SELECT * FROM table_doctor WHERE du_id=$user_id LIMIT 1";
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$fields = '';
			
			if($stmt->rowCount()=='1')
			{
				if(isset($this->_request['name']) && $this->_request['name']!="")
				{
					$fields .=", du_name='".$this->_request['name']."'";
				}
				if(isset($this->_request['mobile_number']) && $this->_request['mobile_number']!="")
				{
					$fields .=", du_phone_no='".$this->_request['mobile_number']."'";
				}
				if(isset($this->_request['address']) && $this->_request['address']!="")
				{
					$fields .=", du_address='".$this->_request['address']."'";
				}
				if(isset($this->_request['city']) && $this->_request['city']!="")
				{
					$fields .=", du_city='".$this->_request['city']."'";
				}
				if(isset($this->_request['district']) && $this->_request['district']!="")
				{
					$fields .=", du_district='".$this->_request['district']."'";
				}
				if(isset($this->_request['state']) && $this->_request['state']!="")
				{
					$fields .=", du_state='".$this->_request['state']."'";
				}
				if(isset($this->_request['pin_code']) && $this->_request['pin_code']!="")
				{
					$fields .=", du_pincode='".$this->_request['pin_code']."'";
				}
				if(isset($this->_request['qualification']) && $this->_request['qualification']!="")
				{
					$fields .=", qualification='".$this->_request['qualification']."'";
				}
				if(isset($this->_request['specialization']) && $this->_request['specialization']!="")
				{
					$fields .=", specialization='".$this->_request['specialization']."'";
				}
				if(isset($_FILES["fileUpload"]["name"]) && $_FILES["fileUpload"]["name"]!="")
				{
					$fields .=", du_pic='".$_FILES["fileUpload"]["name"]."'";
				}
				$updated_at = date('Y-m-d H:i:s');

				if($user_image){
					$this->imageValidation($_FILES["fileUpload"]["name"]);
				}

				$sql = "UPDATE table_doctor SET updated_at='".$updated_at."' {$fields} WHERE du_id='".$user_id."' ";
				$stmt = $this->db->prepare($sql);
				$update = $stmt->execute();
				$fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);
				if(count($update)==1){
					$error = array('status' => "Success", "msg" => "Profile Updated");
					$this->response($this->json($error), 200);
				}
			} else {
				// When record not found using the given userID
				$error = array('status' => "Failed", "msg" => "Invalid User ID");
				$this->response($this->json($error), 200);
			}
		}
		//End Profile Updation

		//Doctor Set Availability
		private function my_availability() {
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
					if(!isset($_POST['start_date_time'])) {
						$error = array('status' => "Failed", "msg" => "Parameter start_date_time is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['end_date_time'])) {
						$error = array('status' => "Failed", "msg" => "Parameter To end_date_time is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['doct_visit_address'])) {
						$error = array('status' => "Failed", "msg" => "Parameter doct_visit_address is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['doct_visit_state_id'])) {
						$error = array('status' => "Failed", "msg" => "Parameter doct_visit_state_id is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['doct_visit_city_id'])) {
						$error = array('status' => "Failed", "msg" => "Parameter doct_visit_city_id is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['doct_visit_district'])) {
						$error = array('status' => "Failed", "msg" => "Parameter doct_visit_district is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['doct_visit_pincode'])) {
						$error = array('status' => "Failed", "msg" => "Parameter doct_visit_pincode is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['doc_id'])) {
						$error = array('status' => "Failed", "msg" => "Parameter doc_id is required");
						$this->response($this->json($error), 200);
					}

					$doc_id = $this->_request['doc_id'];
					$start_date_time = isset($this->_request['start_date_time']) ? $this->_request['start_date_time'] : '';
					$end_date_time = isset($this->_request['end_date_time']) ? $this->_request['end_date_time'] : '';
					$doct_visit_address = isset($this->_request['doct_visit_address']) ? $this->_request['doct_visit_address'] : '';
					$doct_visit_state_id = isset($this->_request['doct_visit_state_id']) ? $this->_request['doct_visit_state_id'] : '';
					$doct_visit_city_id = isset($this->_request['doct_visit_city_id']) ? $this->_request['doct_visit_city_id'] : '';
					$doct_visit_district = isset($this->_request['doct_visit_district']) ? $this->_request['doct_visit_district'] : '';
					$doct_visit_pincode = isset($this->_request['doct_visit_pincode']) ? $this->_request['doct_visit_pincode'] : '';
					$created_at = date('Y-m-d H:i:s');
					$updated_at = date('Y-m-d H:i:s');

					if(!empty($start_date_time) && !empty($end_date_time) && !empty($doct_visit_address) && !empty($doct_visit_state_id) && !empty($doct_visit_city_id) && !empty($doct_visit_pincode) ) {

						$query = "SELECT `du_pincode` FROM table_doctor WHERE du_id = ".$doc_id." LIMIT 1";
						$statmt = $this->db->prepare($query);
						$statmt->execute();
						$fetch_pincode = $statmt->fetchAll(PDO::FETCH_ASSOC);

						$addedPincode= $fetch_pincode[0]['du_pincode'];

						if($addedPincode != $doct_visit_pincode) {

						$sql = "SELECT `doctor_availability`.`start_date_time`, `doctor_availability`.`end_date_time`,
									`doctor_availability`.`doc_id` FROM doctor_availability
									WHERE `doctor_availability`.`doc_id` = ".$doc_id."
									AND `doctor_availability`.`start_date_time`='".$start_date_time."'
									LIMIT 1";
						$stmt = $this->db->prepare($sql);
						$stmt->execute();
						$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

						$fetch_end_date = !empty($results) ? strtotime($results[0]['end_date_time']) : '';
						$fetch_doc_id = !empty($results) ? strtotime($results[0]['doc_id']) : '';
						$today_date = strtotime(date("Y-m-d"));

							if($today_date > $fetch_end_date){
								$date_query = "UPDATE doctor_availability SET `is_delete`='1' ";
								$execute = $this->db->query($date_query);
							}

							if(empty($results)) {

								if(count($this->createDateRangeArray($start_date_time, $end_date_time))=='1') {
									$sql = "INSERT INTO doctor_availability
									(`doc_id`, `start_date_time`, `end_date_time`, `is_delete`,`doctor_visit_add`,`doctor_visit_state_id`,`doctor_visit_city_id`,`doctor_visit_district`,`doctor_visit_pincode_id`,`created_at`, `updated_at`)
													VALUES
									(".$doc_id.", '".$start_date_time."','".$end_date_time."', '0','".$doct_visit_address."',".$doct_visit_state_id.",".$doct_visit_city_id.",'".$doct_visit_district."','".$doct_visit_pincode."','".$created_at."', '".$updated_at."' )";
									$execute = $this->db->query($sql);

									$error = array('status' => "Success", "msg" => "Data inserted successfully");
									$this->response($this->json($error), 200);
								} else{
											$start_date = date('Y-m-d', strtotime($start_date_time));
											$end_date = date('Y-m-d', strtotime($end_date_time));

											$start_time = date('H:i:s', strtotime($start_date_time));
											$end_time = date('H:i:s', strtotime($end_date_time));

											$dateRange = $this->createDateRangeArray($start_date, $end_date);
									foreach($dateRange as $dr) {
											$sql = "INSERT INTO doctor_availability
											(`doc_id`, `start_date_time`, `end_date_time`, `is_delete`,`doctor_visit_add`,`doctor_visit_state_id`,`doctor_visit_city_id`,`doctor_visit_district`,`doctor_visit_pincode_id`,`created_at`, `updated_at`)
											VALUES
											(".$doc_id.", '".$dr.' '.$start_time."','".$dr.' '.$end_time."', '0','".$doct_visit_address."',".$doct_visit_state_id.",".$doct_visit_city_id.",'".$doct_visit_district."','".$doct_visit_pincode."','".$created_at."', '".$updated_at."' )";
											$execute = $this->db->query($sql);
									}

									$error = array('status' => "Success", "msg" => "Data inserted successfully");
									$this->response($this->json($error), 200);
								}

							} else{
								$error = array('status' => "Failed", "msg" => "Data of this Date already inserted");
								$this->response($this->json($error), 200);
							}

						} else{
							$error = array('status' => "Failed", "msg" => "You cann't use same PINCODE ");
							$this->response($this->json($error), 200);
						}
					} else{
						$error = array('status' => "Failed", "msg" => "Please input all the fields");
						$this->response($this->json($error), 200);
					}
		}
// End Doctor Availability

//Check Doctor Availability
/*		private function check_doctor_availability() {
			if($this->get_request_method() != "GET") {
				$this->response('',406);
			}
					$todays_date = date('Y-m-d');
					$sql = "SELECT `table_doctor`.`du_id`, `table_doctor`.`du_name`, `table_doctor`.`du_email`,
					`table_doctor`.`du_phone_no`, `table_doctor`.`du_pic`, `table_doctor`.`specializaton`,
					`table_doctor`.`qualification`, `doctor_availability`.`start_date_time`,
					`doctor_availability`.`end_date_time`, `doctor_availability`.`doctor_visit_add`,
					`doctor_availability`.`doctor_visit_district`,`doctor_availability`.`doctor_visit_pincode_id`,
					`m_state`.`st_name`, `m_city`.`cty_id`
					FROM table_doctor
					JOIN `doctor_availability` ON `doctor_availability`.`doc_id` = `table_doctor`.`du_id`
					LEFT JOIN m_city ON `m_city`.`cty_id` = `doctor_availability`.`doctor_visit_city_id`
					LEFT JOIN m_state ON `m_state`.`st_id` = `doctor_availability`.`doctor_visit_state_id`
					WHERE `table_doctor`.`du_id` = ".$_GET['doc_id']."  AND `user_type` = 0 LIMIT 1";
					$stmt = $this->db->prepare($sql);
					$searchResult = $stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

						$startDate = $results[0]['start_date'];
						$endDate = $results[0]['end_date'];
					$check_date_in_range = $this->check_in_range($startDate, $endDate, $todays_date);

				if(!empty($check_date_in_range)) {
						$error = array('status' => 'Success', 'msg' => 'Data Found', 'data' => json_encode($results[0]));
						$this->response($this->json($error), 200);
				} else{
					$sql = "SELECT `table_doctor`.`du_id`, `table_doctor`.`du_name`,`table_doctor`.`du_email`,`table_doctor`.`du_phone_no`,`table_doctor`.`du_pic`, `table_doctor`.`specializaton`,`table_doctor`.`qualification`, `table_doctor`.`du_address`, `table_doctor`.`du_city`,`table_doctor`.`du_district`,`table_doctor`.`du_state`,`table_doctor`.`du_pincode` FROM table_doctor WHERE `table_doctor`.`du_id` = ".$_GET['doc_id']."  AND `user_type` = 0 LIMIT 1";
					$stmt = $this->db->prepare($sql);
					$searchResult = $stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$error = array('status' => 'Success', 'msg' => 'Doctor Available', 'data' => json_encode($results[0]));
					$this->response($this->json($error), 200);
				}
		}*/
		private function check_doctor_availability() {
			if($this->get_request_method() != "POST") {
				$this->response('',406);
			}
					$doc_id = $_POST['doc_id'];

						$sql = "SELECT `t_doc`.`du_id`, `t_doc`.`du_name`, `t_doc`.`du_email`,
						`t_doc`.`du_phone_no`, `t_doc`.`du_pic`, `t_doc`.`specializaton`,
						`t_doc`.`qualification`, `doc_ava`.`start_date_time`,
						`doc_ava`.`end_date_time`, `doc_ava`.`doctor_visit_add`,
						`doc_ava`.`doctor_visit_district`,`doc_ava`.`doctor_visit_pincode_id`,
						`m_state`.`st_name`, `m_city`.`cty_id`
						FROM table_doctor AS t_doc
						JOIN `doctor_availability` AS `doc_ava` ON `doc_ava`.`doc_id` = `t_doc`.`du_id`
						LEFT JOIN m_city ON `m_city`.`cty_id` = `doc_ava`.`doctor_visit_city_id`
						LEFT JOIN m_state ON `m_state`.`st_id` = `doc_ava`.`doctor_visit_state_id`
						WHERE `t_doc`.`du_id` = ".$doc_id." LIMIT 1";

					$stmt = $this->db->prepare($sql);
					$searchResult = $stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

				if($results) {
						$startDate = $results[0]['start_date_time'];
						$endDate = $results[0]['end_date_time'];

						$error = array('status' => 'Success', 'msg' => 'Data Found', 'data' => $results[0]);
						$this->response($this->json($error), 200);

				} else{
					$error = array("status" => 'Success', "msg" => 'No Data Found');
					$this->response($this->json($error), 200);
				}
		}
//End Check Doctor Availability
		private function users() {
			// Cross validation if the request method is GET else it will return "Not Acceptable" status
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$sql = mysql_query("SELECT user_id, user_fullname, user_email FROM users WHERE user_status = 1", $this->db);
			if(mysql_num_rows($sql) > 0){
				$result = array();
				while($rlt = mysql_fetch_array($sql,MYSQL_ASSOC)){
					$result[] = $rlt;
				}
				// If success everythig is good send header as "OK" and return list of users in JSON format
				$this->response($this->json($result), 200);
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function deleteUser(){
			// Cross validation if the request method is DELETE else it will return "Not Acceptable" status
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){
				mysql_query("DELETE FROM users WHERE user_id = $id");
				$success = array('status' => "Success", "msg" => "Successfully one record deleted.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// If no records "No Content" status
		}
		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}

}

	// Initiiate Library
	$api = new API;
	$api->processApi();
?>
