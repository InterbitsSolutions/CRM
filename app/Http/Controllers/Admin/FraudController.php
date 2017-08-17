<?php
namespace App\Http\Controllers\Admin;

use App\FraudIP;
use App\Models\FraudParams;
use App\Models\BucketLeadArea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use App\Models\MasterBuckets;

class FraudController extends Controller
{
	
		public function browser_name()
		{
			$ua = $_SERVER['HTTP_USER_AGENT'];
			if (
				strpos(strtolower($ua), 'safari/') &&
				strpos(strtolower($ua), 'opr/')
			) {
				// Opera
				$res = 'Opera';
			} elseif (
				strpos(strtolower($ua), 'safari/') &&
				strpos(strtolower($ua), 'chrome/')
			) {
				// Chrome
				$res = 'Chrome';
			} elseif (
				strpos(strtolower($ua), 'msie') ||
				strpos(strtolower($ua), 'trident/')
			) {
				// Internet Explorer
				$res = 'Internet Explorer';
			} elseif (strpos(strtolower($ua), 'firefox/')) {
				// Firefox
				$res = 'Firefox';
			} elseif (
				strpos(strtolower($ua), 'safari/') &&
				(strpos(strtolower($ua), 'opr/') === false) &&
				(strpos(strtolower($ua), 'chrome/') === false)
			) {
				// Safari
				$res = 'Safari';
			} else {
				// Out of data
				$res = false;
			}
			return $res;
		}
		public function  user_ip()
		{
			$ipaddress = '';
			if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
			else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			else
			$ipaddress = 'UNKNOWN';
			return $ipaddress;
		}
		 /*
        * function to get user area
        * created by NK
        * created on 2 August
		* @help URL : http://ourcodeworld.com/articles/read/51/how-to-detect-the-country-of-a-visitor-in-php-or-javascript-for-free-with-the-request-ip
        */
        public function capture_bucket_lead_info($bucketId,$bname)
        {
            $logDate                        = date('Y-m-d');
			if($bname=="")
			{
				$bucketName                     = "";
				$MasterBucketsCount             = MasterBuckets::where('bucket_pid',$bucketId)->select('bucket_name')->count();
				if($MasterBucketsCount>0)
				{
					$MasterBucketsArr           = MasterBuckets::where('bucket_pid',$bucketId)->select('bucket_name')->first();
					$bucketName                 = $MasterBucketsArr['bucket_name'];
				}
			}
			else
			{
				$bucketName = $bname;
			}
            $customerIP                     = $this->user_ip();
            $ipArray 			            = json_decode(file_get_contents('https://ipapi.co/'.$customerIP.'/json/'));
            $latitude			            = $ipArray->latitude;
            $longitude			            = $ipArray->longitude;
            $city				            = $ipArray->city;
			
			/*$ipArray 						= json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$customerIP));
			$latitude			            = $ipArray->geoplugin_latitude;
            $longitude			            = $ipArray->geoplugin_longitude;
            $city				            = $ipArray->geoplugin_city;*/
			
            $bucketLeadArea                 = new BucketLeadArea;
            $bucketLeadArea->bucket_name    = $bucketName;
            $bucketLeadArea->bucket_pid     = $bucketId;
            $bucketLeadArea->log_counter    = 1;
            $bucketLeadArea->log_date       = $logDate;
            $bucketLeadArea->customer_ip    = $customerIP;
            $bucketLeadArea->latitude       = $latitude;
            $bucketLeadArea->longitude      = $longitude;
            $bucketLeadArea->city   	    = $city;
            $bucketLeadArea->browser  	    = $this->browser_name();
            $bucketLeadArea->save();
            $message = "Information has been added successfully and redirect to BUCKET url.";
            //return response
            $return = array(
                'value' => '1',
                'type' => 'success',
                'message' => $message,
            );
            return json_encode($return);
        }
		
		
		/*
		 * function to get customer IP
		 */
		public function customer_ip()
        {
            if(!empty($_GET)){
                //get params
                $maxLogCounter = 5;
                $logDate = date('Y-m-d');
                $customerIP = $_SERVER['REMOTE_ADDR'];             
                $campaignID = (!empty($_GET['campaign_id'])) ? $_GET['campaign_id'] : "";
                $pid 		= (!empty($_GET['pid'])) ? $_GET['pid'] : "";

                //check if params found or not
                if(!empty($campaignID)){               
                        //check if entry exist for campaign and user ip address
                        $checkFraudRecordExist = FraudIP::where('campaign_id', "=", $campaignID)->where('customer_ip', '=', $customerIP)->where('log_date', '=', $logDate)->first();
                        $analyticsIdArr		   = MasterBuckets::where('bucket_pid', "=", $pid)->first();
						$analyticsId		   = $analyticsIdArr['bucket_analytics_id'];
						$sendAnalyticsCode     = "";						
						//add new record
                        if(empty($checkFraudRecordExist)){
                            $fraudIP               = new FraudIP;							
							$ipArray 			   = json_decode(file_get_contents('https://ipapi.co/112.196.40.98/json/'));	
							$latitude			   = $ipArray->latitude;	   	
							$longitude			   = $ipArray->longitude;	  
							$city				   = $ipArray->city;	  							
                            $fraudIP->campaign_id  = $campaignID;
                            $fraudIP->log_counter  = $fraudIP->log_counter+1;
                            $fraudIP->log_date     = $logDate;
                            $fraudIP->customer_ip  = $customerIP;
                            $fraudIP->latitude     = $latitude;
                            $fraudIP->longitude    = $longitude;
                            $fraudIP->city   	   = $city;
                            $fraudIP->browser  	   = $this->browser_name();
                            $fraudIP->save();								
                            $message = "Information has been added successfully and redirect to BUCKET url.";
                            //return response
                            $return = array(
                                'value' => '1',
                                'type' => 'success',
                                'message' => $message,
                                'redirect_url' => 'bucket_url',
                                'analytics_code' =>  $analyticsId,
                            );
                            return json_encode($return);
                        }else{
                            $logCounter = $checkFraudRecordExist['log_counter'];
                            if($logCounter<$maxLogCounter){ //check max log counter for single IP
                                //update record
                                $recordID = $checkFraudRecordExist['id'];
                                $fraudIP               = FraudIP::find($recordID);
                                $fraudIP->log_counter  = $fraudIP->log_counter+1;
                                $fraudIP->save();
                                $message = "Information has been updated successfully and redirect to BUCKET url.";
                                //return response
                                $return = array(
                                    'value' => '1',
                                    'type' => 'success',
                                    'message' => $message,
                                    'redirect_url' => 'bucket_url',
                                    'analytics_code' =>  $analyticsId,
                                );
                                return json_encode($return);
                            }else{
                                $message = "Customer already access the link for $maxLogCounter times and redirect to SAFE url.";                               
								//echo "<script>window.location.href='https://www.facebook.com/'</script>";                              
									$return = array(
									'value' => '2',
									'type' => 'success',
									'message' => $message,
									);
									return json_encode($return);								
                            }
                        }                    
                }else{
                    $message = "There is some error in the params provided by you, please check!";
					//return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);
                }
            }else{
                $message = "No Params found, please check!";
				//echo "<script>window.location.href='https://www.facebook.com/'</script>";		
                //return response
               $return = array(
                    'value' => '100',
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            }
        }

    /*
     * function to get Phone Number on the basis of PID
     * created by BK
     * created on 14th June'17
     */
    public function getPidPhone(){
        $pid = (!empty($_REQUEST['pid'])) ? $_REQUEST['pid'] : 0;
        $phoneNumber = '';
        if(!empty($pid)){
            $getPidDetails = MasterBuckets::where('bucket_pid', "=", $pid)->first();
            $phoneNumber = $getPidDetails['bucket_phone_number'];
        }
        //return response
        $return = array(
            'type' => (!empty($phoneNumber) ? 'success' : 'error'),
            'pid_phone' => (!empty($phoneNumber) ? $phoneNumber : ''),
        );
        return json_encode($return);
    }

    // Function to get the client IP address
    public function getClientIP() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /*
    * function to save Bucket Params
    */
    public function saveParams()
    {
        if(!empty($_GET)){
            //get params
            $maxLogCounter = 5;
            $logDate = date('Y-m-d');
            $customerIP = $this->getClientIP();
            $campaignID = (!empty($_GET['cid'])) ? $_GET['cid'] : "";
            $pid 		= (!empty($_GET['pid'])) ? $_GET['pid'] : "";
            $bid 		= (!empty($_GET['bid'])) ? $_GET['bid'] : "";
            $ip 		= (!empty($_GET['ip'])) ? $_GET['ip'] : "";
            $city 		= (!empty($_GET['city'])) ? $_GET['city'] : "";
            $network    = (!empty($_GET['network'])) ? $_GET['network'] : "";
            //check if params found or not
            if(!empty($campaignID)){
                //check if entry exist for campaign and user ip address
                $checkFraudRecordExist = FraudParams::where('cid', "=", $campaignID)->where('customer_ip', '=', $customerIP)->where('log_date', '=', $logDate)->first();
                $analyticsIdArr		   = MasterBuckets::where('bucket_pid', "=", $pid)->first();
                $analyticsId		   = $analyticsIdArr['bucket_analytics_id'];
                $sendAnalyticsCode     = "";
                //add new record
                if(empty($checkFraudRecordExist)){
                    $ipString = "https://ipapi.co/$customerIP/json/";
                    //add entry in Fraud IP
                    $fraudIP               = new FraudParams;
                    $ipArray 			   = json_decode(file_get_contents($ipString));
                    $latitude			   = $ipArray->latitude;
                    $longitude			   = $ipArray->longitude;
//                    $city				   = $ipArray->city;
                    $fraudIP->cid          = $campaignID;
                    $fraudIP->log_counter  = $fraudIP->log_counter+1;
                    $fraudIP->log_date     = $logDate;
                    $fraudIP->customer_ip  = $customerIP;
//                    $fraudIP->latitude     = $latitude;
//                    $fraudIP->longitude    = $longitude;
                    $fraudIP->city   	   = $city;
                    $fraudIP->pid   	   = $pid;
                    $fraudIP->bid   	   = $bid;
                    $fraudIP->network      = $network;
                    $fraudIP->browser  	   = $this->browser_name();
                    $fraudIP->save();
                    $message = "Information has been added successfully and redirect to BUCKET url.";
                    //return response
                    $return = array(
                        'value' => '1',
                        'type' => 'success',
                        'message' => $message,
                        'redirect_url' => 'bucket_url',
                        'analytics_code' =>  $analyticsId,
                    );
                    return json_encode($return);
                }else{
                    $logCounter = $checkFraudRecordExist['log_counter'];
                    if($logCounter<$maxLogCounter){ //check max log counter for single IP
                        //update record
                        $recordID = $checkFraudRecordExist['id'];
                        $fraudIP               = FraudParams::find($recordID);
                        $fraudIP->log_counter  = $fraudIP->log_counter+1;
                        $fraudIP->save();
                        $message = "Information has been updated successfully and redirect to BUCKET url.";
                        //return response
                        $return = array(
                            'value' => '1',
                            'type' => 'success',
                            'message' => $message,
                            'redirect_url' => 'bucket_url',
                            'analytics_code' =>  $analyticsId,
                        );
                        return json_encode($return);
                    }else{
                        $message = "Customer already access the link for $maxLogCounter times and redirect to SAFE url.";
                        //echo "<script>window.location.href='https://www.facebook.com/'</script>";
                        $return = array(
                            'value' => '2',
                            'type' => 'success',
                            'message' => $message,
                        );
                        return json_encode($return);
                    }
                }
            }else{
                $message = "There is some error in the params provided by you, please check!";
                //return response
                $return = array(
                    'value' => '100',
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            }
        }else{
            $message = "No Params found, please check!";
            //echo "<script>window.location.href='https://www.facebook.com/'</script>";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }
	/*
     * function to get analytics id
     *
     */
    public function getAnalyticsId($pid)
    {
        $analyticsIdData = MasterBuckets::where('bucket_pid', "=", $pid)->first();
        $analyticsId     = $analyticsIdData['bucket_analytics_id'];
        $script ="";
        $script.="<script>";
        $script.="(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');";
        $script.="ga('create', '$analyticsId', 'auto');";
        $script.="ga('send', 'pageview');";
        $script.="</script>";
        echo $script;
   }
	
	
	public function webAnalytics()
	{
		return view('adminsOnly.webAnalytics.web-analytics');
	}
	
	
}
