<?php
require_once('../../../wp-load.php');

class PS_Interakt_Syncing_Data
{
	/**
	*@var bool $isUserCount Holds value for counting total no. of users or not
	*/

	private $isUserCount;

	/**
	*@var int $totalUsers Holds value of total no. of users
	*/

	private	$totalUsers;
	
	/**
	*@var int $totalSyncedUsers Holds value of No. of users whose data has been synced
	*/

	private	$totalSyncedUsers;

	/**
	*@var int $syncUserCount Count for users whose data has been sent to interakt
	*/
	private $syncUserCount;

	/**
	*@var int $interaktAppId App Id for interakt api
	*/
	private $interaktAppId;

	/**
	*@var int $interaktAppKey App Key for interakt api
	*/
	private $interaktAppKey;

	/**
	*@var array $userData To hold the user data
	*/
	private $userData=array();

	public function __construct()
	{
		$this->isUserCount=isset($_POST['isUserCount'])?$_POST['isUserCount']:"";
		$this->totalUsers=isset($_POST['totalUsers'])?$_POST['totalUsers']:"";
		$this->interaktAppId=isset($_POST['interaktAppId'])?$_POST['interaktAppId']:"";
		$this->interaktAppKey=isset($_POST['interaktAppKey'])?$_POST['interaktAppKey']:"";

	}
	
	/**
	*Send total no. of users and start syncing process
	*/

	public function startSyncingProcess()
	{
		if($this->isUserCount=='yes')
		{
			$this->countTotalUser();
			echo $this->totalUsers;
		}
		else
		{
			$this->totalSyncedUsers=$this->getSyncedUsers();
			if(($this->totalUsers-$this->totalSyncedUsers)>0)
			{
				$this->fetchingUserData($this->totalSyncedUsers);
				echo $this->getSyncedUsers();
			}
			else
			{
				echo "synced";
			}
		}
	}

	/**
	*Count total no. of users in the database
	*/

	public function countTotalUser()
	{
		global $wpdb;
		$query="SELECT count(*) FROM $wpdb->users";
		$this->totalUsers=$wpdb->get_var($query);
	}
	/**
	*Retreiving user data from database
	*/
	public function fetchingUserData($offset)
	{
		global $wpdb;
		$this->syncUserCount=0;
		$query="";
		if($offset==0)
		{
			$query="SELECT user_email,user_registered,display_name FROM $wpdb->users ORDER BY ID LIMIT 30";
		}
		else
		{
			$query="SELECT user_email,user_registered,display_name FROM $wpdb->users ORDER BY ID LIMIT 30 OFFSET $offset";
		}
		
		$wp_user_search = $wpdb->get_results($query);
		foreach ( $wp_user_search as $userid )
		{
			$email=stripslashes($userid->user_email);
			$name=stripslashes($userid->display_name);
			$created_at=stripcslashes($userid->user_registered);
			$this->userData=array('email'=>$email,'name'=>$name,'created_at'=>$created_at);
			$this->userData=json_encode($this->userData);
			$this->sendData();
			$this->syncUserCount++;
		}
		$this->syncedUsers();
	}
	/**
	*Sending user data to interakt api through curl
	*/
	public function sendData()
	{
		$url='https://app.interakt.co/api/v1/members';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl,CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
		curl_setopt($curl, CURLOPT_USERPWD, $this->interaktAppId.':'.$this->interaktAppKey);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->userData);
		$curl_response = curl_exec($curl);
		curl_close($curl);
	}
	/**
	*Store the no. of users in the database whose data hase been synced
	*/
	public function syncedUsers()
	{
		$this->totalSyncedUsers=get_option('syncedUsers');
		if($this->totalSyncedUsers==false)
		{
			update_option('syncedUsers',$this->syncUserCount);
		}
		else
		{
			$this->totalSyncedUsers+=$this->syncUserCount;
			update_option('syncedUsers',$this->totalSyncedUsers);
		}
	}
	/**
	*Returns the no. of users whose data has been synced
	*/
	public function getSyncedUsers()
	{
		$synced_users=get_option('syncedUsers')!=false?get_option('syncedUsers'):0;
		return $synced_users;
	}

}
$syncing_object=new PS_Interakt_Syncing_Data();
$syncing_object->startSyncingProcess();
?>