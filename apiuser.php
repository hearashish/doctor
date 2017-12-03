<?php
error_reporting(E_ALL & ~E_NOTICE);
	require_once("Rest.inc.php");
	class APIuser extends REST {

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
			//$this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
			$this->db = new PDO('mysql:host=e2e-28-62.e2enetworks.net.in;dbname=db_doctor', 'root', 'rEuL5eFcLe91HdJw', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );
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
								$error = array('status' => "Failed", "msg" => "jpg, png, jpeg are required");
								return $this->response($this->json($error), 200);
							}
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

		//User Registeration
				public function user_registeration() {
					// Cross validation if the request method is POST else it will return "Not Acceptable" status
					if($this->get_request_method() != "POST"){
						$this->response('',406);
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
					$created_at = date('Y-m-d H:i:s');
					$updated_at = date('Y-m-d H:i:s');

					//Upload image
					if($user_image){
						$this->imageValidation($_FILES["fileUpload"]["name"]);
					}

					if($phone_no || $email) {
							$checkphoneExists = "SELECT user_id FROM `table_user` WHERE (`user_phone_no` = '".$phone_no."') OR (`user_email` = '".$email."')";
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

											$sql = "INSERT INTO table_user (`user_name`, `user_email`, `user_phone_no`, `user_pic`, `user_password`, `user_address`, `user_city`, `user_district`, `user_state`, `user_pincode`, `remember_token`, `created_at`, `updated_at`)
											VALUES ('".$name."','".$email."',".$phone_no.", '".$user_image."' , '".$hashed_password."', '".$address."', '".$city."', '".$district."', '".$state."', '".$pincode."','".$token_value."', '".$created_at."','".$updated_at."' )";

											$execute = $this->db->query($sql);

											$error = array('status' => "Sucess", "msg" => "User Successfully created!");
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

// Login of User
		private function user_login() {
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
					$sql = "SELECT user_id, user_name, user_email, user_phone_no, user_pic, user_address, remember_token
									FROM table_user
									WHERE user_email = '$email'
									AND user_password = '".$hashed_password."'
									LIMIT 1";
					$stmt = $this->db->prepare($sql);
					$stmt->execute();
				  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if($stmt->rowCount()=='0') {
              $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
              $this->response($this->json($error), 200);
            }
            				if($results[0]['user_pic']==''){
						$error = array('status' => "Success", "msg" => "Sucessfully Login!", "data" => $results );
					} else{
						$results[0]['user_pic']='images/'.$results[0]['user_pic'];
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
// End of Login User

		private function user_reset_password(){
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
			if(!isset($_POST['user_id']) ) {
				$error = array('status' => "Failed", "msg" => "Parameter user_id are require");
				$this->response($this->json($error), 200);
			}

			$user_id = $this->_request['user_id'];
			$password = $this->_request['password'];
			$cpassword = $this->_request['confirm_password'];

			if(!empty($password) && !empty($cpassword) ) {

				if($password!=$cpassword) {
					$error = array('status' => "Failed", "msg" => "Password and Confirm password is not matched");
					$this->response($this->json($error), 200);

				} else{
						$hashed_password = sha1($password);
						$sql = "UPDATE table_user SET user_password='".$hashed_password."' WHERE user_id='".$user_id."' ";
						$stmt = $this->db->prepare($sql);
						$update = $stmt->execute();
						$fetchData = $stmt->fetchAll(PDO::FETCH_ASSOC);

						if(count($update)==1){
							$error = array('status' => "Success", "msg" => "Profile Updated");
							$this->response($this->json($error), 200);
						}

				}
			}
		}

		private function user_profile_updation() {
			$user_id = $this->_request['user_id'];

			$sql = "SELECT user_name,user_email,user_phone_no,user_address,user_city,user_district,user_state,user_pincode FROM table_user WHERE user_id=$user_id LIMIT 1";
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$fields = '';

			if($stmt->rowCount()=='1')
			{
				if(isset($this->_request['name']) && $this->_request['name']!="")
				{
					$fields .=", user_name='".$this->_request['name']."'";
				}
				if(isset($this->_request['mobile_number']) && $this->_request['mobile_number']!="")
				{
					$fields .=", user_phone_no='".$this->_request['mobile_number']."'";
				}
				if(isset($this->_request['address']) && $this->_request['address']!="")
				{
					$fields .=", user_address='".$this->_request['address']."'";
				}
				if(isset($this->_request['city']) && $this->_request['city']!="")
				{
					$fields .=", user_city='".$this->_request['city']."'";
				}
				if(isset($this->_request['district']) && $this->_request['district']!="")
				{
					$fields .=", user_district='".$this->_request['district']."'";
				}
				if(isset($this->_request['state']) && $this->_request['state']!="")
				{
					$fields .=", user_state='".$this->_request['state']."'";
				}
				if(isset($this->_request['pin_code']) && $this->_request['pin_code']!="")
				{
					$fields .=", user_pincode='".$this->_request['pin_code']."'";
				}
				if(isset($_FILES["fileUpload"]["name"]) && $_FILES["fileUpload"]["name"]!="")
				{
					$fields .=", user_pic='".$_FILES["fileUpload"]["name"]."'";
				}
				$updated_at = date('Y-m-d H:i:s');

				if($user_image){
					$this->imageValidation($_FILES["fileUpload"]["name"]);
				}

				$sql = "UPDATE table_user SET updated_at='".$updated_at."' {$fields} WHERE user_id='".$user_id."' ";
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

				//Doctor Search
		private function doctor_search()
		{
			if($this->get_request_method() != "POST"){
			$this->response('',406);
			}
			$name = isset($this->_request['name'])? '%'.$this->_request['name'].'%':'';
			$phone_no = isset($this->_request['mobile_number']) ? '%'.$this->_request['mobile_number'].'%':'';
			$speacializaton = isset($this->_request['specializaton'])? '%'.$this->_request['specializaton'].'%':'';
			$pincode = isset($this->_request['pincode']) ? '%'.$this->_request['pincode'].'%' :'';

			try
			{
				$where = " WHERE 1";
				$where1 = "";
				if(isset($this->_request['name']) && $this->_request['name']!='')
				{
					$where .=" AND du.du_name like '%".$this->_request['name']."%'";
				}
				if(isset($this->_request['mobile_number']) && $this->_request['mobile_number']!='')
				{
					$where .=" AND du.du_phone_no like '%".$this->_request['mobile_number']."%'";
				}
				if(isset($this->_request['specialization']) && $this->_request['specialization']!='')
				{
					$where .=" AND du.specialization like '%".$this->_request['specialization']."%'";
				}
				if(isset($this->_request['pincode']) && $this->_request['pincode']!='')
				{
					$where .=" AND du.du_pincode like '%".$this->_request['pincode']."%'";
				}
				$query="SELECT du.du_id, du.du_name, du.du_phone_no,
				du.du_pic, du.du_address ,du.du_city, du.du_district, du.du_state, du.du_pincode, du.specialization,
				du.qualification FROM table_doctor du
				LEFT JOIN m_city mc ON du.du_city = mc.cty_id
				LEFT JOIN m_state ms ON du.du_state = ms.st_id {$where}";

				$stmt = $this->db->prepare($query);
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$error = array('status' => "Sucess", "msg" =>"Availability Data", "data" => $result);
				$this->response($this->json($error), 200);
			}
			catch(Exception $ex)
			{
				$error = array('status' => "Failed", "msg" =>$ex->getMessage());
				$this->response($this->json($error), 200);
			}
		}
		//End Doctor Search

		//Fetch particular record of doctor
		public function doctor_availability() {
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$doc_id = '';
			$doc_id = $_GET['doc_id'];
			if($doc_id){
				$doctor_id = $_GET['doc_id'];
				$sql = "SELECT du.du_name, du.du_email, du.du_phone_no, du.du_pic, du.du_address,
				du.du_city, du.du_district, du.du_state, du.du_pincode, du.specialization, du.qualification,
				da.start_date_time, da.end_date_time, da.doctor_visit_add, da.doctor_visit_district,
				da.doctor_visit_pincode_id
				FROM table_doctor as du
				LEFT JOIN doctor_availability as da ON da.doc_id = du.du_id
				WHERE du.du_id = $doctor_id ";

				$stmt = $this->db->prepare($sql);
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$error = array('status' => "Sucess", "msg" =>"Availability Data", "data" => $result);
				$this->response($this->json($error), 200);
			}
		}
		
				
		// Fix appoitnment with doctor
		private function fix_appointment() {
			if($this->get_request_method() != "POST") {
				$this->response('',406);
			}

			if(!isset($_POST['user_id'])) {
				$error = array('status' => "Failed", "msg" => "Parameter user_id is required");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['doctor_id'])) {
				$error = array('status' => "Failed", "msg" => "Parameter doctor_id is required");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['appointment_date'])) {
				$error = array('status' => "Failed", "msg" => "Parameter appointment_date is required");
				$this->response($this->json($error), 200);
			}
			if(!isset($_POST['appointment_time'])) {
				$error = array('status' => "Failed", "msg" => "Parameter appointment_time is required");
				$this->response($this->json($error), 200);
			}

			$user_id = isset($this->_request['user_id']) ? $this->_request['user_id'] : '';
			$doctor_id = isset($this->_request['doctor_id']) ? $this->_request['doctor_id'] : '';
			$appointment_date = isset($this->_request['appointment_date']) ? $this->_request['appointment_date'] : '';
			$appointment_time = isset($this->_request['appointment_time']) ? $this->_request['appointment_time'] : '';
			$created_at = date('Y-m-d H:i:s');;
			$updated_at = date('Y-m-d H:i:s');;

			/*if(isset($user_id)) {
							$sql = "SELECT `doctor_ava_time` FROM doctor_availability WHERE `doctor_id` = ".$doctor_id." ";
							$stmt = $this->db->prepare($sql);
							$searchResult = $stmt->execute();
							$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


							foreach($results as $res){
								$res = array_merge($res);
									if(in_array($_POST['doctor_availability_time'], $res)) {
										$error = array('status' => "Failed", "msg" => "Please select another time as doctor is busy");
										$this->response($this->json($error), 200);
									}*/
	//Lunch time
						$lunch_st = "01:00:00";
						$lunch_end = "02:00:00";
						$inuput_val = DateTime::createFromFormat('H:i:s', $appointment_time);
						$lts = DateTime::createFromFormat('H:i:s', $lunch_st);
						$lte = DateTime::createFromFormat('H:i:s', $lunch_end);
//echo $inuput_val;
						if($inuput_val >= $lts && $inuput_val <= $lte) {
							$error = array('status' => "Failed", "msg" => "Lunch time, please select another time");
							$this->response($this->json($error), 200);
						}

$query_timeslot = "INSERT INTO doctor_timeslot (`doctor_id`, `patient_id`, `doctor_ava_time`, `doctor_ava_date` ,`created_at`, `updated_at`) VALUES ( ".$doctor_id.", ".$user_id." , '".$appointment_time."', '".$appointment_date."', '".$created_at."', '".$updated_at."' );";
$execute_query = $this->db->query($query_timeslot);
echo $execute_query; die;
							if($execute_query) {
								$error = array('status' => "Sucess", "msg" => "Appoitment fixed");
								$this->response($this->json($error), 200);
							}
					//}
		//}
		}
		//End docotor record

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
	$api = new APIuser;
	$api->processApi();
?>
