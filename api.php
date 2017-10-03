<?php


	require_once("Rest.inc.php");

	class API extends REST {

		public $data = "";

		const DB_SERVER = "localhost";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "db_api";

		private $db = NULL;

		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}

	function createDateRangeArray($start, $end) {
		// Modified by JJ Geewax
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
		 *  Database connection
		*/
		private function dbConnect(){
			//$this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
			$this->db = new PDO('mysql:host=localhost; dbname=doctor','root','root');
			//if($this->db)
				//mysql_select_db(self::DB,$this->db);
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

		/*
		 *	Simple login API
		 *  Login must be POST method
		 *  email : <USER EMAIL>
		 *  pwd : <USER PASSWORD>
		 */
		public function getToken($length){
		    $token = "";
		    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		    $codeAlphabet.= "0123456789";
		    $max = strlen($codeAlphabet); // edited

		   for ($i=0; $i < $length; $i++) {
		       $token .= $codeAlphabet[random_int(0, $max-1)];
		   }
		   return $token;
		}
//Doctor Set Availability
		private function my_availability(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			if(isset($_GET['doc_id'])) {
					if(!isset($_POST['start_date'])) {
						$error = array('status' => "Failed", "msg" => "Parameter start_Date is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['end_date'])) {
						$error = array('status' => "Failed", "msg" => "Parameter To end_date is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['from_time'])) {
						$error = array('status' => "Failed", "msg" => "Parameter from_time is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['to_time'])) {
						$error = array('status' => "Failed", "msg" => "Parameter to_time is required");
						$this->response($this->json($error), 200);
					}

					$doc_id = $_GET['doc_id'];
					$start_date = isset($this->_request['start_date']) ? $this->_request['start_date'] : '';
					$end_date = isset($this->_request['end_date']) ? $this->_request['end_date'] : '';
					$from_time = isset($this->_request['from_time']) ? $this->_request['from_time'] : '';
					$to_time = isset($this->_request['to_time']) ? $this->_request['to_time'] : '';
					$created_at = date('Y-m-d H:i:s');
					$updated_at = date('Y-m-d H:i:s');

					$dateRange = $this->createDateRangeArray($start_date, $end_date);
		//Count date in difference
			/*$diff = abs(strtotime($end_date) - strtotime($start_date));

			$years = floor($diff / (365*60*60*24));
			$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));*/

					if(!empty($start_date) && !empty($end_date) && !empty($from_time) && !empty($to_time) ) {

						$sql = "SELECT doc_id,start_date,end_date,from_time,to_time FROM doc_availability WHERE doc_id = ".$doc_id." and start_date='".$start_date."' LIMIT 1";
echo $sql;
//tomorrow work to be done
						$stmt = $this->db->prepare($sql);
						$stmt->execute();
					  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
//print_r($results); exit();
						if(empty($results)) {

							if(count($this->createDateRangeArray($start_date, $end_date))=='1') {
								$sql = "INSERT INTO doc_availability (`doc_id`, `start_date`, `end_date`, `from_time`, `to_time`, `created_at`, `updated_at`) VALUES (".$doc_id.",'".$start_date."','".$end_date."', '".$from_time."', '".$to_time."', '".$created_at."', '".$updated_at."' )";
								$execute = $this->db->query($sql);

								$error = array('status' => "Failed", "msg" => "Data inserted successfully");
								$this->response($this->json($error), 200);
							} else{
								foreach($dateRange as $dr) {
										$sql = "INSERT INTO doc_availability (`doc_id`, `start_date`, `end_date`, `from_time`, `to_time`, `created_at`, `updated_at`) VALUES (".$doc_id.",'".$dr."','".$dr."', '".$from_time."', '".$to_time."', '".$created_at."', '".$updated_at."' )";
										$execute = $this->db->query($sql);
								}
								$error = array('status' => "Failed", "msg" => "Data inserted successfully");
								$this->response($this->json($error), 200);
							}
						} else{
							$error = array('status' => "Failed", "msg" => "Data of this Date already inserted");
							$this->response($this->json($error), 200);
						}

					} else{
						$error = array('status' => "Failed", "msg" => "Please input all the fields");
						$this->response($this->json($error), 200);
					}
			}else{
				$error = array('status' => "Failed", "msg" => "No Doctor found");
				$this->response($this->json($error), 200);
			}
		}
// End Doctor Availability

// Login of Doctor and User
		private function login() {
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
					$sql = "SELECT du_name,du_email,du_phone_no,du_pic,du_address,remember_token FROM doctor_user WHERE du_email = '$email' AND du_password = '".$hashed_password."' LIMIT 1";
					$stmt = $this->db->prepare($sql);
					$stmt->execute();
				  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
          //$results = $stmt->fetchAll(PDO::FETCH_OBJ);
          if($stmt->rowCount()=='0') {
              $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
              $this->response($this->json($error), 200);
            }
						$error = array('status' => "Success", "msg" => "Sucessfully Login!", "data" => json_encode($results) );
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

//http://localhost/myapi/api.php?value=registeration&user_id=0
// Registeration
		public function registeration() {
			/*$c = "Hello World";
			$this->uploadImage($c);
			exit();*/
			// Cross validation if the request method is POST else it will return "Not Acceptable" status
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			if(!isset($_POST['password']) && !isset($_POST['email']) && !isset($_POST['name']) && !isset($_POST['confirm_password']) && !isset($_POST['mobile_number']) ) {
				$error = array('status' => "Failed", "msg" => "Parameter name, email, mobile no,password and confirm_password are require");
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
				$error = array('status' => "Failed", "msg" => "Parameter Confirm password is required");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['qualification'])) {
				$error = array('status' => "Failed", "msg" => "Parameter qualification is required");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['specializaton'])) {
				$error = array('status' => "Failed", "msg" => "Parameter specializaton is required");
				$this->response($this->json($error), 200);
			}

			$name = $this->_request['name'];
			$email = $this->_request['email'];
			$password = $this->_request['password'];
			$cpassword = $this->_request['confirm_password'];
			$phone_no = $this->_request['mobile_number'];
			$user_image = isset($_FILES["fileUpload"]["name"]) ? $_FILES["fileUpload"]["name"] : '';
			$address = $this->_request['address'];
			$city = isset($this->_request['city']) ? $this->_request['city'] : '';
			$district = isset($this->_request['district']) ? $this->_request['district'] : '';
			$state = isset($this->_request['state']) ? $this->_request['state'] : '';
			$pincode = isset($this->_request['pincode']) ? $this->_request['pincode'] : '';
			$qualification = isset($this->_request['qualification']) ? $this->_request['qualification'] : '';
			$specializaton = isset($this->_request['specializaton']) ? $this->_request['specializaton'] : '';
			$created_at = date('Y-m-d H:i:s');
			$updated_at = date('Y-m-d H:i:s');

			if($user_image){
				$image_name = uniqid();
				$uploads_dir='images';
				$tmp_name = $_FILES["fileUpload"]["tmp_name"];
				$user_image = basename($_FILES["fileUpload"]["name"]);
				move_uploaded_file($tmp_name, "$uploads_dir/$name");
			}
			$checkphoneExists = "Select du_id FROM `doctor_user` where (`du_phone_no` = '".$phone_no."') OR (`du_email` = '".$email."')";
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

							if($_GET['user_id']=='1') {  // Client Data Store
									$sql = "INSERT INTO doctor_user (`du_name`, `du_email`, `du_phone_no`, `du_pic`, `user_type`, `du_password`, `du_address`, `du_city`, `du_district`, `du_state`, `du_pincode` ,`remember_token`, `specializaton`, `qualification`,`created_at`, `updated_at`) VALUES ('".$name."','".$email."',".$phone_no.", '".$user_image."' , '1','".$hashed_password."', '".$address."', '".$city."', '".$district."', '".$state."', ".$pincode.",'".$token_value."', '', '', '".$created_at."', '".$updated_at."' )";
									$execute = $this->db->query($sql);

									$error = array('status' => "Sucess", "msg" => "User Successfully created!");
									$this->response($this->json($error), 200);
							  }
								if($_GET['user_id']=='0') { // Doctor Data Store

							    $sql = "INSERT INTO doctor_user (`du_name`, `du_email`, `du_phone_no`, `du_pic`, `user_type`, `du_password`, `du_address`, `du_city`, `du_district`, `du_state`, `du_pincode` ,`remember_token`, `specializaton`, `qualification`, `created_at`, `updated_at`) VALUES ('".$name."', '".$email."', ".$phone_no.", '".$user_image."', '0','".$hashed_password."', '".$address."', '".$city."', '".$district."', '".$state."', '".$pincode."', '".$token_value."', '".$specializaton."', '".$qualification."', '".$created_at."', '".$updated_at."')";
									$execute = $this->db->query($sql);

									$error = array('status' => "Sucess", "msg" => "Doctor Successfully created!");
									$this->response($this->json($error), 200);
							  }

					}
			} else{
			$error = array('status' => "Failed", "msg" => "Email ID or Phone number already exists");
			$this->response($this->json($error), 200);
		}

			// If invalid inputs "Bad Request" status message and reason
			$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			$this->response($this->json($error), 200);
	}
// End Registeration

// Profile Updation
		private function profile_updation() {
			$user_id = $_GET['user_id'];

			$sql = "SELECT * FROM doctor_user WHERE du_id=$user_id LIMIT 1";
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if($stmt->rowCount()=='1') {

					if(!isset($_POST['name'])) {
						$error = array('status' => "Failed", "msg" => "Parameter name is required");
						$this->response($this->json($error), 200);
					}
					/*else if(!isset($_POST['password'])) {
						$error = array('status' => "Failed", "msg" => "Parameter password is required");
						$this->response($this->json($error), 200);
					}
					else if(!isset($_POST['confirm_password'])) {
						$error = array('status' => "Failed", "msg" => "Parameter Confirm password is required");
						$this->response($this->json($error), 200);
					}*/
					else if(!isset($_POST['address'])) {
						$error = array('status' => "Failed", "msg" => "Parameter address is required");
						$this->response($this->json($error), 200);
					}
					/*else if( $_POST['password'] != $_POST['confirm_password'] ) {
						$error = array('status' => "Failed", "msg" => "Password not matched");
						$this->response($this->json($error), 200);
					}*/
					else if(isset($_POST['specializaton'])=='') {
						$error = array('status' => "Failed", "msg" => "Parameter specializaton is required");
						$this->response($this->json($error), 200);
					}
					else {
						// Declare all the post variable.
						$name = $this->_request['name'];
						//$password = $this->_request['password'];
						//$cpassword = $this->_request['confirm_password'];
						$phone_no = $this->_request['mobile_number'];
						$address = $this->_request['address'];
						$city = $this->_request['city'];
						$district = $this->_request['district'];
						$state = $this->_request['state'];
						$pin_code = $this->_request['pin_code'];
						$qualification = $this->_request['qualification'];
						$user_image = isset($_FILES["fileUpload"]["name"]) ? $_FILES["fileUpload"]["name"] : '';
						$updated_at = date('Y-m-d H:i:s');

						if($user_image){
							$image_name = uniqid();
							$uploads_dir='images';
							$tmp_name = $_FILES["fileUpload"]["tmp_name"];
							$user_image = basename($_FILES["fileUpload"]["name"]);
							move_uploaded_file($tmp_name, "$uploads_dir/$name");
						}

						if(!empty($name) && !empty($phone_no)) {
// Generate Hashed Password
						$hashed_password = sha1($_POST['password']);
							/*$sql = "UPDATE doctor_user SET `du_name`='".$name."', `du_phone_no`=".$phone_no.",
											du_pic='".$user_image."' , `du_password`='".$hashed_password."',
											`du_address`='".$address."', `qualification`='".$qualification."',
											`du_city`='".$city."', `du_district`='".$district."', `du_state`='".$state."',
											'".$pincode."', `updated_at`='".$updated_at."', `du_pic`=''
											WHERE du_id='".$user_id."' ";*/
							$sql = "UPDATE doctor_user SET `du_name`='".$name."', `du_phone_no`=".$phone_no.",
											du_pic='".$user_image."' , `du_address`='".$address."', `qualification`='".$qualification."',
											`du_city`='".$city."', `du_district`='".$district."', `du_state`='".$state."',
											'".$pincode."', `updated_at`='".$updated_at."', `du_pic`=''
											WHERE du_id='".$user_id."' ";

							$stmt = $this->db->prepare($sql);
							$update = $stmt->execute();
							$fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);

								if(count($update)==1){
									$error = array('status' => "Success", "msg" => "Profile Updated");
									$this->response($this->json($error), 200);
								}
							} else if(empty($phone_no)){
								$error = array('status' => "Success", "msg" => "Phone is Required");
								$this->response($this->json($error), 200);
							}
								else{
								$error = array('status' => "Success", "msg" => "Name is Required");
								$this->response($this->json($error), 200);
							}
					}
// When record not found using the given userID
				} else {
					$error = array('status' => "Failed", "msg" => "Invalid User ID");
					$this->response($this->json($error), 200);
				}

		}
//End Profile Updation

//http://localhost/myapi/api.php?value=doctor_search
//Doctor Search
		private function doctor_search() {
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
		//Declare POST variable
			$name = isset($this->_request['name'])? '%'.$this->_request['name'].'%':'';
			$phone_no = isset($this->_request['mobile_number']) ? '%'.$this->_request['mobile_number'].'%':'';
			$speacializaton = isset($this->_request['specializaton'])? '%'.$this->_request['specializaton'].'%':'';
			$pincode = isset($this->_request['pincode']) ? '%'.$this->_request['pincode'].'%' :'';

			// Execute Query
		if(isset($this->_request['name'])) {
			$sql = "SELECT `du_name`,`du_email`,`du_phone_no`,`du_pic`,`du_address`,`du_city`,`du_district`,`du_state`,`du_pincode`,`specializaton`,`qualification` FROM doctor_user WHERE `user_type` = 0 AND `du_name` LIKE '$name' LIMIT 5 ";
		} else if(isset($this->_request['mobile_number'])) {
			$sql = "SELECT `du_name`,`du_email`,`du_phone_no`,`du_pic`,`du_address`,`du_city`,`du_district`,`du_state`,`du_pincode`,`specializaton`,`qualification` FROM doctor_user WHERE `user_type` = 0 AND `du_phone_no` LIKE '$phone_no' LIMIT 5";
		} else if(isset($this->_request['specializaton'])) {
			$sql = "SELECT `du_name`,`du_email`,`du_phone_no`,`du_pic`,`du_address`,`du_city`,`du_district`,`du_state`,`du_pincode`,`specializaton`,`qualification` FROM doctor_user WHERE `user_type` = 0 AND `specializaton` LIKE '$speacializaton' LIMIT 5";
		} else{
			$sql = "SELECT `du_name`,`du_email`,`du_phone_no`,`du_pic`,`du_address`,`du_city`,`du_district`,`du_state`,`du_pincode`,`specializaton`,`qualification` FROM doctor_user WHERE `user_type` = 0 AND `du_pincode` LIKE '".$pincode."' LIMIT 5";
		}

			$stmt = $this->db->prepare($sql);
			$searchResult = $stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if(count($results)>0) {
				$error = array('status' => "Sucess", "msg" => count($results)." Result found", "Data" => json_encode($results) );
				$this->response($this->json($error), 200);
			} else{
				$error = array('status' => "Failed", "msg" => "No Result Found");
				$this->response($this->json($error), 200);
			}

		}
//End Doctor Search

//Check Doctor Availability
		private function check_doc_availability(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			if(!isset($_GET['doc_id'])){
				$error = array('status' => "Failed", "msg" => "Doctor id is missing");
				$this->response($this->json($error), 200);
			} else{
					$sql = "SELECT `doctor_user`.`du_name`,`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doctor_user`.`du_address`,`doctor_user`.`du_city`,`doctor_user`.`du_district`,`doctor_user`.`du_state`,`doctor_user`.`du_pincode`,`doctor_user`.`specializaton`,`doctor_user`.`qualification`, `doc_availability`.`start_date`, `doc_availability`.`end_date`, `doc_availability`.`end_date`, `doc_availability`.`from_time`, `doc_availability`.`to_time`
									FROM doctor_user
									JOIN doc_availability ON `doc_availability`.`doc_id` = `doctor_user`.`du_id`
									WHERE `doctor_user`.`du_id` = ".$_GET['doc_id']."  AND `user_type` = 0 LIMIT 1";
					$stmt = $this->db->prepare($sql);
					$searchResult = $stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

					$error = array('status' => "Success", "msg" => "Data Found", "data" => json_encode($results[0]));
					$this->response($this->json($error), 200);
			}

		}
//End Check Doctor Availability


		private function fix_appoitnment(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$user_id = $_GET['user_id'];
			$doc_id = $_GET['doctor_id'];
			$created_at = date('Y-m-d H:i:s');
			$updated_at = date('Y-m-d H:i:s');

			if(isset($user_id)) {
					$availability = $this->_request['doctor_availability_time'];

					if(!isset($_POST['doctor_availability_time'])){
						$error = array('status' => "Failed", "msg" => "Parameter specializaton is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($availability) && $availability=''){
						$error = array('status' => "Failed", "msg" => "Time is required");
						$this->response($this->json($error), 200);
					} else{
							$sql = "SELECT `doctor_ava_time` FROM doctor_timeslot WHERE `doctor_id` = ".$doc_id." ";
							$stmt = $this->db->prepare($sql);
							$searchResult = $stmt->execute();
							$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	//If time is selected by another user
							foreach($results as $res){
								$res = array_merge($res);
									if(in_array($_POST['doctor_availability_time'], $res)) {
										$error = array('status' => "Failed", "msg" => "Please select another time as doctor is busy");
										$this->response($this->json($error), 200);
									}

							}

	//Lunch time
						$lunch_st = "01:00:00";
						$lunch_end = "02:00:00";

						$inuput_val = DateTime::createFromFormat('H:i:s', $_POST['doctor_availability_time']);
						$lts = DateTime::createFromFormat('H:i:s', $lunch_st);
						$lte = DateTime::createFromFormat('H:i:s', $lunch_end);

						if($inuput_val >= $lts && $inuput_val <= $lte) {
							$error = array('status' => "Failed", "msg" => "Lunch time, please select another time");
							$this->response($this->json($error), 200);
						}

						$sql = "INSERT INTO doctor_timeslot (`doctor_id`, `doctor_ava_time`, `patient_id`, `created_at`, `updated_at`) VALUES ( ".$doc_id.",'".$_POST['doctor_availability_time']."',".$user_id.", '".$created_at."', '".$updated_at."')";
						$execute = $this->db->query($sql);
						print_r($execute);
							if($execute) {
								$error = array('status' => "Sucess", "msg" => "Appoitment fixed");
								$this->response($this->json($error), 200);
							}
					}

			} else{
				$error = array('status' => "Failed", "msg" => "Session expired");
				$this->response($this->json($error), 200);
			}

		}

		private function users(){
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
