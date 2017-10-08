<?php


	require_once("Rest.inc.php");

	class API extends REST {

		public $data = "";

		const DB_SERVER = "localhost";
		const DB_USER = "root";
		const DB_PASSWORD = "root";
		const DB = "doctor";

		private $db = NULL;

		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}

	function createDateRangeArray($start, $end) {
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
		public function getToken($length) {
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
			if(!isset($_POST['mobile_number'])) {
				$error = array('status' => "Failed", "msg" => "Parameter mobile_number is required");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['doctor_user_id'])) {
				$error = array('status' => "Failed", "msg" => "Parameter doctor_user_id is required");
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
			$specializaton = isset($this->_request['specializaton']) ? $this->_request['specializaton'] : '';
			$doctor_user_id = $this->_request['doctor_user_id'];
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

							if($doctor_user_id=='1') {  // Client Data Store
									$sql = "INSERT INTO doctor_user (`du_name`, `du_email`, `du_phone_no`, `du_pic`, `user_type`, `du_password`, `du_address`, `du_city`, `du_district`, `du_state`, `du_pincode` ,`remember_token`, `specializaton`, `qualification`,`created_at`, `updated_at`) VALUES ('".$name."','".$email."',".$phone_no.", '".$user_image."' , '1','".$hashed_password."', '".$address."', '".$city."', '".$district."', '".$state."', '".$pincode."','".$token_value."', '', '', '".$created_at."','".$updated_at."' )";
									$execute = $this->db->query($sql);

									$error = array('status' => "Sucess", "msg" => "User Successfully created!");
									$this->response($this->json($error), 200);
							  }

								if($doctor_user_id == '0') { // Doctor Data Store
							    $sql = "INSERT INTO doctor_user (`du_name`, `du_email`, `du_phone_no`, `du_pic`, `user_type`, `du_password`, `du_address`, `du_city`, `du_district`, `du_state`, `du_pincode` ,`remember_token`, `specializaton`, `qualification`, `created_at`, `updated_at`) VALUES ('".$name."', '".$email."', ".$phone_no.", '".$user_image."', '0','".$hashed_password."', '".$address."', '".$city."', '".$district."', '".$state."', '".$pincode."', '".$token_value."', '".$specializaton."', '".$qualification."', '".$created_at."', '".$updated_at."')";
									$execute = $this->db->query($sql);

									$error = array('status' => "Sucess", "msg" => "Doctor Successfully created!");
									$this->response($this->json($error), 200);
							  }
					} else{
					$error = array('status' => "Failed", "msg" => "Name, Email password and confirm_password required");
					$this->response($this->json($error), 200);
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

			$user_id = $this->_request['doctor_user_id'];

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
						$city = isset($this->_request['city']) ? $this->_request['city'] : '';
						$district = isset($this->_request['district']) ? $this->_request['district'] : '';
						$state = isset($this->_request['state']) ? $this->_request['state'] : '';
						$pin_code = isset($this->_request['pin_code']) ? $this->_request['pin_code'] : '';
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
						//$hashed_password = sha1($_POST['password']);
							/*$sql = "UPDATE doctor_user SET `du_name`='".$name."', `du_phone_no`=".$phone_no.",
											du_pic='".$user_image."' , `du_password`='".$hashed_password."',
											`du_address`='".$address."', `qualification`='".$qualification."',
											`du_city`='".$city."', `du_district`='".$district."', `du_state`='".$state."',
											'".$pincode."', `updated_at`='".$updated_at."', `du_pic`=''
											WHERE du_id='".$user_id."' ";*/
							$sql = "UPDATE doctor_user SET `du_name`='".$name."', du_pic='".$user_image."' , `du_address`='".$address."', `qualification`='".$qualification."', `du_city`='".$city."', `du_district`='".$district."', `du_state`='".$state."', `du_pincode`='".$pin_code."', `updated_at`='".$updated_at."', `du_pic`='' WHERE du_id='".$user_id."' ";
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
			/*if(!isset($_POST['doctor_name'])) {
				$error = array('status' => "Failed", "msg" => "Parameter doctor_name is required");
				$this->response($this->json($error), 200);
			}*/
//Declare POST variable
			$name = isset($this->_request['name'])? '%'.$this->_request['name'].'%':'';
			$phone_no = isset($this->_request['mobile_number']) ? '%'.$this->_request['mobile_number'].'%':'';
			$speacializaton = isset($this->_request['specializaton'])? '%'.$this->_request['specializaton'].'%':'';
			$pincode = isset($this->_request['pincode']) ? '%'.$this->_request['pincode'].'%' :'';

// Fetch Doctor records who have add their availability
			$ava_sql = "SELECT `doc_availability`.`start_date`, `doc_availability`.`end_date`,`doc_availability_place`.`doc_id` FROM doc_availability
									JOIN `doc_availability_place` ON `doc_availability_place`.`id` = `doc_availability`.`doc_ava_pla_id`
									JOIN `doctor_user` ON `doctor_user`.`du_id`= `doc_availability_place`.`doc_id`
									WHERE `doc_availability`.`start_date`= CURRENT_DATE()
									OR `doc_availability`.`end_date`=CURRENT_DATE()
									AND `doc_availability`.`is_delete`='0'
									AND `doctor_user`.`user_type`='0' ";

			$stmt = $this->db->prepare($ava_sql);
			$searchResult = $stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

			//If Not Empty then fetch doc id
			if(!empty($results)) {
				foreach($results as $ress) {
					$check_doc_ava[] = $ress['doc_id'];
			}
			$check_doc_val = isset($check_doc_ava) ? implode(',',$check_doc_ava) : '' ;
				if(!empty($_POST['doctor_name'])) {

					$name = $_POST['doctor_name'];
					//fetch data who's not put their availability
					$ava_not_sql = "SELECT `doctor_user`.`du_name`,`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doctor_user`.`du_address`,`doctor_user`.`du_city`,`doctor_user`.`du_district`,`doctor_user`.`du_state`,`doctor_user`.`du_pincode` FROM doctor_user
								WHERE `doctor_user`.`du_id`
								NOT IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`du_name`
								LIKE '$name' LIMIT 5 ";

					$stmt = $this->db->prepare($ava_not_sql);
					$searchResult = $stmt->execute();
					$results_not_ava = $stmt->fetchAll(PDO::FETCH_ASSOC);

					$ava_sql1 = "SELECT DISTINCT(`doctor_user`.`du_name`),`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doc_availability_place`.`doct_visit_address`,`doc_availability_place`.`doct_visit_state`,`doc_availability_place`.`doct_visit_city`,`doc_availability_place`.`doct_visit_district`,`doc_availability_place`.`doct_visit_pincode` FROM `doctor_user`
							JOIN `doc_availability_place` ON `doc_availability_place`.`doc_id` = `doctor_user`.`du_id`
								WHERE `doctor_user`.`du_id` IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`du_name`
								LIKE '%".$name."%' LIMIT 5 ";

					$stmnt = $this->db->prepare($ava_sql1);
					$searchResults = $stmnt->execute();
					$results_avalable = $stmnt->fetchAll(PDO::FETCH_ASSOC);

					//if( !empty($results_not_ava) && !empty($results_avalable) ){
						$error = array('status' => "Success", 'msg' => "Doctor Result Found", 'data' => array_merge($results_not_ava,$results_avalable));
						$this->response($this->json($error), 200);
					/*} else{
						$error = array('status' => "Success", 'msg' => "0 Result Found" );
						$this->response($this->json($error), 200);
					}*/

				} else if(!empty($_POST['mobile_number'])) {

					$mobile_name = $_POST['mobile_number'];
					$ava_not_sql = "SELECT `doctor_user`.`du_name`,`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doctor_user`.`du_address`,`doctor_user`.`du_city`,`doctor_user`.`du_district`,`doctor_user`.`du_state`,`doctor_user`.`du_pincode` FROM doctor_user
								WHERE `doctor_user`.`du_id`
								NOT IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`du_phone_no`
								LIKE ".$mobile_name." LIMIT 5 ";

					$stmt = $this->db->prepare($ava_not_sql);
					$searchResult = $stmt->execute();
					$results_not_ava = $stmt->fetchAll(PDO::FETCH_ASSOC);

					$ava_sql1 = "SELECT DISTINCT(`doctor_user`.`du_name`),`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doc_availability_place`.`doct_visit_address`,`doc_availability_place`.`doct_visit_state`,`doc_availability_place`.`doct_visit_city`,`doc_availability_place`.`doct_visit_district`,`doc_availability_place`.`doct_visit_pincode` FROM `doctor_user`
							JOIN `doc_availability_place` ON `doc_availability_place`.`doc_id` = `doctor_user`.`du_id`
								WHERE `doctor_user`.`du_id` IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`du_name`
								LIKE ".$mobile_name." LIMIT 5 ";

					$stmnt = $this->db->prepare($ava_sql1);
					$searchResults = $stmnt->execute();
					$results_avalable = $stmnt->fetchAll(PDO::FETCH_ASSOC);

					//if( !empty($results_not_ava) && !empty($results_avalable) ){
						$error = array('status' => "Success", 'msg' => "Mobile Result Found", 'data' => json_encode(array_merge($results_not_ava,$results_avalable)) );
						$this->response($this->json($error), 200);
					/*} else{
						$error = array('status' => "Success", 'msg' => "0 Result Found" );
						$this->response($this->json($error), 200);
					}*/

				} else if(!empty($_POST['specializaton'])) {

					$specializaton = $_POST['specializaton'];
					$ava_not_sql = "SELECT `doctor_user`.`du_name`,`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doctor_user`.`du_address`,`doctor_user`.`du_city`,`doctor_user`.`du_district`,`doctor_user`.`du_state`,`doctor_user`.`du_pincode` FROM doctor_user
								WHERE `doctor_user`.`du_id`
								NOT IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`specializaton`
								LIKE '".$specializaton."' LIMIT 5 ";

					$stmt = $this->db->prepare($ava_not_sql);
					$searchResult = $stmt->execute();
					$results_not_ava = $stmt->fetchAll(PDO::FETCH_ASSOC);

					$ava_sql1 = "SELECT DISTINCT(`doctor_user`.`du_name`),`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doc_availability_place`.`doct_visit_address`,`doc_availability_place`.`doct_visit_state`,`doc_availability_place`.`doct_visit_city`,`doc_availability_place`.`doct_visit_district`,`doc_availability_place`.`doct_visit_pincode` FROM `doctor_user`
							JOIN `doc_availability_place` ON `doc_availability_place`.`doc_id` = `doctor_user`.`du_id`
								WHERE `doctor_user`.`du_id` IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`specializaton`
								LIKE '".$specializaton."' LIMIT 5 ";

					$stmnt = $this->db->prepare($ava_sql1);
					$searchResults = $stmnt->execute();
					$results_avalable = $stmnt->fetchAll(PDO::FETCH_ASSOC);

					//if( !empty($results_not_ava) && !empty($results_avalable) ){
						$error = array('status' => "Success", 'msg' => "Specializaton Result Found", 'data' => json_encode(array_merge($results_not_ava,$results_avalable)) );
						$this->response($this->json($error), 200);
					/*} else{
						$error = array('status' => "Success", 'msg' => "0 Result Found" );
						$this->response($this->json($error), 200);
					}*/

				} else if(!empty($_POST['qualification'])) {

					$qualification = $_POST['qualification'];
					$ava_not_sql = "SELECT `doctor_user`.`du_name`,`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doctor_user`.`du_address`,`doctor_user`.`du_city`,`doctor_user`.`du_district`,`doctor_user`.`du_state`,`doctor_user`.`du_pincode` FROM doctor_user
								WHERE `doctor_user`.`du_id`
								NOT IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`qualification`
								LIKE '".$qualification."' LIMIT 5 ";

					$stmt = $this->db->prepare($ava_not_sql);
					$searchResult = $stmt->execute();
					$results_not_ava = $stmt->fetchAll(PDO::FETCH_ASSOC);

					$ava_sql1 = "SELECT DISTINCT(`doctor_user`.`du_name`),`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doc_availability_place`.`doct_visit_address`,`doc_availability_place`.`doct_visit_state`,`doc_availability_place`.`doct_visit_city`,`doc_availability_place`.`doct_visit_district`,`doc_availability_place`.`doct_visit_pincode` FROM `doctor_user`
							JOIN `doc_availability_place` ON `doc_availability_place`.`doc_id` = `doctor_user`.`du_id`
								WHERE `doctor_user`.`du_id` IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`qualification`
								LIKE '".$qualification."' LIMIT 5 ";

					$stmnt = $this->db->prepare($ava_sql1);
					$searchResults = $stmnt->execute();
					$results_avalable = $stmnt->fetchAll(PDO::FETCH_ASSOC);

					//if( !empty($results_not_ava) && !empty($results_avalable) ){
						$error = array('status' => "Success", 'msg' => "Qualification Result Found", 'data' => json_encode(array_merge($results_not_ava,$results_avalable)) );
						$this->response($this->json($error), 200);
					/*} else{
						$error = array('status' => "Success", 'msg' => "0 Result Found" );
						$this->response($this->json($error), 200);
					}*/

				} else if(!empty($_POST['pincode'])) {

					$pincode = $_POST['pincode'];
					$ava_not_sql = "SELECT `doctor_user`.`du_name`,`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doctor_user`.`du_address`,`doctor_user`.`du_city`,`doctor_user`.`du_district`,`doctor_user`.`du_state`,`doctor_user`.`du_pincode` FROM doctor_user
								WHERE `doctor_user`.`du_id`
								NOT IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`du_pincode`
								LIKE ".$pincode." LIMIT 5 ";

					$stmt = $this->db->prepare($ava_not_sql);
					$searchResult = $stmt->execute();
					$results_not_ava = $stmt->fetchAll(PDO::FETCH_ASSOC);

					$ava_sql1 = "SELECT DISTINCT(`doctor_user`.`du_name`),`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doc_availability_place`.`doct_visit_address`,`doc_availability_place`.`doct_visit_state`,`doc_availability_place`.`doct_visit_city`,`doc_availability_place`.`doct_visit_district`,`doc_availability_place`.`doct_visit_pincode` FROM `doctor_user`
							JOIN `doc_availability_place` ON `doc_availability_place`.`doc_id` = `doctor_user`.`du_id`
								WHERE `doctor_user`.`du_id` IN ($check_doc_val)
								AND `doctor_user`.`user_type`='0'
								AND `doctor_user`.`du_pincode`
								LIKE ".$pincode." LIMIT 5 ";

					$stmnt = $this->db->prepare($ava_sql1);
					$searchResults = $stmnt->execute();
					$results_avalable = $stmnt->fetchAll(PDO::FETCH_ASSOC);

					//if( !empty($results_not_ava) && !empty($results_avalable) ){
						$error = array('status' => "Success", 'msg' => "Pin Code Result Found", 'data' => json_encode(array_merge($results_not_ava,$results_avalable)) );
						$this->response($this->json($error), 200);
					/*} else{
						$error = array('status' => "Success", 'msg' => "0 Result Found" );
						$this->response($this->json($error), 200);
					}*/
			}

		} else {
				$ava_sql1 = "SELECT `doctor_user`.`du_name`,`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`,`doctor_user`.`du_address`,`doctor_user`.`du_city`,`doctor_user`.`du_district`,`doctor_user`.`du_state`,`doctor_user`.`du_state`,`doctor_user`.`specializaton`,`doctor_user`.`qualification` FROM `doctor_user`
							WHERE `doctor_user`.`user_type`='0'
							LIMIT 5 ";

				$stmnt = $this->db->prepare($ava_sql1);
				$searchResults = $stmnt->execute();
				$results_avalable = $stmnt->fetchAll(PDO::FETCH_ASSOC);

				//if( !empty($results_not_ava) && !empty($results_avalable) ){
					$error = array('status' => "Success", 'msg' => "Result Found", 'data' => json_encode($results_avalable) );
					$this->response($this->json($error), 200);
				/*} else{
					$error = array('status' => "Success", 'msg' => "0 Result Found" );
					$this->response($this->json($error), 200);
				}*/

			}
}
//End Doctor Search

//http://localhost/myapi/api.php?value=my_availability&doc_id=3
//Doctor Set Availability
		private function my_availability() {
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
					if(!isset($_POST['address_to_visit'])) {
						$error = array('status' => "Failed", "msg" => "Parameter address_to_visit is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['doct_visit_state'])) {
						$error = array('status' => "Failed", "msg" => "Parameter doct_visit_state is required");
						$this->response($this->json($error), 200);
					}
					if(!isset($_POST['doct_visit_city'])) {
						$error = array('status' => "Failed", "msg" => "Parameter doct_visit_city is required");
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

					$doc_id = $_GET['doc_id'];
					$start_date = isset($this->_request['start_date']) ? $this->_request['start_date'] : '';
					$end_date = isset($this->_request['end_date']) ? $this->_request['end_date'] : '';
					$from_time = isset($this->_request['from_time']) ? $this->_request['from_time'] : '';
					$to_time = isset($this->_request['to_time']) ? $this->_request['to_time'] : '';

					$doct_visit_address = isset($this->_request['address_to_visit']) ? $this->_request['address_to_visit'] : '';
					$doct_visit_state = isset($this->_request['doct_visit_state']) ? $this->_request['doct_visit_state'] : '';
					$doct_visit_city = isset($this->_request['to_time']) ? $this->_request['doct_visit_city'] : '';
					$doct_visit_district = isset($this->_request['to_time']) ? $this->_request['doct_visit_district'] : '';
					$doct_visit_pincode = isset($this->_request['to_time']) ? $this->_request['doct_visit_pincode'] : '';

					$created_at = date('Y-m-d H:i:s');
					$updated_at = date('Y-m-d H:i:s');

					$dateRange = $this->createDateRangeArray($start_date, $end_date);
		//Count date in difference
			/*$diff = abs(strtotime($end_date) - strtotime($start_date));

			$years = floor($diff / (365*60*60*24));
			$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));*/

					if(!empty($start_date) && !empty($end_date) && !empty($from_time) && !empty($to_time) && !empty($doct_visit_address) && !empty($doct_visit_state) && !empty($doct_visit_city) && !empty($doct_visit_pincode) ) {

						$sql = "SELECT `doc_availability`.`start_date`, `doc_availability`.`end_date`, `doc_availability`.`from_time`, `doc_availability`.`to_time`, `doc_availability_place`.`doc_id` FROM doc_availability INNER JOIN `doc_availability_place` ON `doc_availability_place`.`id` = `doc_availability`.`doc_ava_pla_id` WHERE `doc_availability_place`.`doc_id` = ".$doc_id." and `doc_availability`.`start_date`='".$start_date."' LIMIT 1";
						$stmt = $this->db->prepare($sql);
						$stmt->execute();
					  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

						/*$check_doc_availability = "SELECT doc_id,end_date FROM doc_availability WHERE doc_id = ".$doc_id." AND is_delete='0' LIMIT 1";
						$stmt = $this->db->prepare($check_doc_availability);
						$stmt->execute();
					  $records = $stmt->fetchAll(PDO::FETCH_ASSOC);*/

						$fetch_end_date = !empty($results) ? strtotime($results[0]['end_date']) : '';
						$fetch_doc_id = !empty($results) ? strtotime($results[0]['doc_id']) : '';
						$today_date = strtotime(date("Y-m-d"));

						if($today_date > $fetch_end_date){
							$date_query = "UPDATE doc_availability SET `is_delete`='1' ";
							$execute = $this->db->query($date_query);
						}

						if(empty($results)) {
							if(count($this->createDateRangeArray($start_date, $end_date))=='1') {

								$sql = "INSERT INTO doc_availability_place (`doc_id`, `doct_visit_address`, `doct_visit_district`,`doct_visit_city`, `doct_visit_state`, `doct_visit_pincode`)
												VALUES (".$doc_id.", '".$doct_visit_address."', '".$doct_visit_district."', '".$doct_visit_state."' , '".$doct_visit_city."' , '".$doct_visit_pincode."' )";
								$execute = $this->db->query($sql);

								$sql = "SELECT id FROM doc_availability_place ORDER BY id DESC LIMIT 1";
								$stmt = $this->db->prepare($sql);
								$searchResult = $stmt->execute();
								$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
								$doc_availability_place_id = $results[0]['id'];

								$sql = "INSERT INTO doc_availability (`doc_ava_pla_id`, `start_date`, `end_date`, `from_time`, `to_time`, `created_at`, `updated_at`)
												VALUES (".$doc_availability_place_id.",'".$start_date."','".$end_date."', '".$from_time."', '".$to_time."', '".$created_at."', '".$updated_at."' )";
								$execute = $this->db->query($sql);

								/*$sql_query = "INSERT doc_availability_place SET `doct_visit_address`='".$doct_visit_address."', `doct_visit_city`='".$doct_visit_city."', `doct_visit_district`='".$doct_visit_district."',`doct_visit_state`='".$doct_visit_state."',`doct_visit_pincode`='".$doct_visit_pincode."' ";
								$execute = $this->db->query($sql_query);*/

								$error = array('status' => "Success", "msg" => "Data inserted successfully");
								$this->response($this->json($error), 200);

							} else{
								$sql = "INSERT INTO doc_availability_place (`doc_id`, `doct_visit_address`, `doct_visit_district`,`doct_visit_city`, `doct_visit_state`, `doct_visit_pincode`)
												VALUES (".$doc_id.", '".$doct_visit_address."', '".$doct_visit_district."', '".$doct_visit_state."' , '".$doct_visit_city."' , '".$doct_visit_pincode."' )";
								$execute = $this->db->query($sql);

								$sql = "SELECT id FROM doc_availability_place ORDER BY id DESC LIMIT 1";
								$stmt = $this->db->prepare($sql);
								$searchResult = $stmt->execute();
								$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
								$doc_availability_place_id = $results[0]['id'];

								foreach($dateRange as $dr) {
										$sql = "INSERT INTO doc_availability
										(`doc_ava_pla_id`, `start_date`, `end_date`, `from_time`, `to_time`, `created_at`, `updated_at`)
										VALUES (".$doc_availability_place_id.",'".$dr."','".$dr."', '".$from_time."', '".$to_time."', '".$created_at."', '".$updated_at."' )";

										$execute = $this->db->query($sql);
								}
								/*$sql_query = "UPDATE doctor_user SET `du_address`='".$doct_visit_address."', `du_city`='".$doct_visit_city."', `du_district`='".$doct_visit_district."',`du_state`='".$doct_visit_state."',`du_pincode`='".$doct_visit_pincode."' WHERE `du_id` = ".$doc_id." ";
								$execute = $this->db->query($sql_query);*/

								$error = array('status' => "Success", "msg" => "Data inserted successfully");
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


//http://localhost/myapi/api.php?value=check_doc_availability&doc_id=3
//Check Doctor Availability
		private function check_doc_availability() {
			if($this->get_request_method() != "GET") {
				$this->response('',406);
			}

			if(!isset($_GET['doc_id'])) {
				$error = array('status' => "Failed", "msg" => "Doctor id is missing");
				$this->response($this->json($error), 200);
			} else{
					$todays_date = date('Y-m-d');

					$sql = "SELECT `doctor_user`.`du_id`, `doctor_user`.`du_name`,`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`, `doctor_user`.`specializaton`,`doctor_user`.`qualification`, `doc_availability`.`start_date`, `doc_availability`.`end_date`, `doc_availability`.`from_time`, `doc_availability`.`to_time`, `doc_availability_place`.`doct_visit_address`, `doc_availability_place`.`doct_visit_state`, `doc_availability_place`.`doct_visit_city`,`doc_availability_place`.`doct_visit_district`,`doc_availability_place`.`doct_visit_pincode` FROM doctor_user
					JOIN `doc_availability_place` ON `doc_availability_place`.`doc_id` = `doctor_user`.`du_id`
					JOIN `doc_availability` ON `doc_availability`.`doc_ava_pla_id` = `doc_availability_place`.`id`
					WHERE `doctor_user`.`du_id` = ".$_GET['doc_id']."  AND `user_type` = 0 LIMIT 1";

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

					$sql = "SELECT `doctor_user`.`du_id`, `doctor_user`.`du_name`,`doctor_user`.`du_email`,`doctor_user`.`du_phone_no`,`doctor_user`.`du_pic`, `doctor_user`.`specializaton`,`doctor_user`.`qualification`, `doctor_user`.`du_address`, `doctor_user`.`du_city`,`doctor_user`.`du_district`,`doctor_user`.`du_state`,`doctor_user`.`du_pincode` FROM doctor_user WHERE `doctor_user`.`du_id` = ".$_GET['doc_id']."  AND `user_type` = 0 LIMIT 1";
					$stmt = $this->db->prepare($sql);
					$searchResult = $stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

					$error = array('status' => 'Success', 'msg' => 'Doctor Available', 'data' => json_encode($results[0]));
					$this->response($this->json($error), 200);
				}
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
