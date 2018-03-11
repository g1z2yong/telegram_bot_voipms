<?PHP
require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;


//$db = new SQLite3('sms.db');
require_once("1api/API/5/class.voipms.php");

$redis = new Redis();
$redis->connect('127.0.0.1');

function parse_ini()
{
  $ini_array = parse_ini_file("user.ini");
  return $ini_array;
}

$array =  parse_ini();

function list_contact()
{
   global $redis;
   $total='';
   $array=$redis->sMembers('contact');
   foreach ($array as &$name) {
      $phone=get_contact($name);
      $s=sprintf("%s :%s\n",$name,$phone);
     $total.=$s;
  }
  return $total;
}

//var_dump(list_contact());

function get_contact($name_string)
{
  global $redis;
 if($redis->sIsMember('contact' , $name_string))
 {
	return $redis->get($name_string);
 }else{
	return -1;
 }
}

function add_contact($one_string)
{
   global $redis;
   $out=preg_split('/( )/',trim($one_string));
   $name=$out[0];
   $phone_number=$out[count($out)-1];
   $redis->sAdd('contact',$name);
   $redis->set($name,$phone_number);
}

//add_contact('jie              7788597232');
//print_r(get_contact('zyguo'));

function onestrsms($str)
{
   global $redis,$array;
   $from=$array["from"];
   //$from='7788260163';
   $out=explode(" ",$str);
   $to=$out[0];
   $message=implode(" ",array_slice($out,1));
   $cname=get_contact($to);
   if(strlen($cname)>1)
   {
	$to=$cname;
	$res=onesms($from,$to,$message);
	return $res;
   }
   if(strlen($to)==10)
   {
   printf("%s %s %s",$from,$to,$message);
   $res=onesms($from,$to,$message);
   }else
   {$res="Error : $to is wrong .Phone Number is 10 long,like 7781234567";
   }
   return $res;
}

function  onesms($from,$to,$message)
{
	$signature="SMS From";
	$voipms = new VoIPms();
	$message=$message."\n".$signature;
	$response = $voipms->sendSMS($from,$to,$message);
        var_dump($response['status']);
	return $response['status'];
}


//onestrsms("zyguo KKOS gigigi sss");


function savesms($from,$to,$message)
{
  $db = new SQLite3('zyguo_sms.db');
  $sql="insert into sms values (DateTime('now'),'$from','$to','$message')";
  echo $sql;
  $results = $db->query($sql);
}

function get_phone_id($chat_id)
{
  $db = new SQLite3('zyguo_sms.db');
  $sql="select phone_id from reg where chat_id='$chat_id'";
  $results = $db->query($sql);
  $row = $results->fetchArray();
  return $row['phone_id'];

}
function get_chat_id($phone_id)
{
  $db = new SQLite3('zyguo_sms.db');
  $sql="select chat_id from reg where phone_id='$phone_id'";
  $results = $db->query($sql);
  $row = $results->fetchArray();
  return $row['chat_id'];
}
function update_chat_id($phone_id,$chat_id)
{
  $db = new SQLite3('zyguo_sms.db');
  $sql="update reg set chat_id = '$chat_id' where phone_id='$phone_id'";
  $results = $db->query($sql);
}

function update_reg($chat_id)
{
 $redis = new Redis();
 $redis->connect('127.0.0.1');
 $redis->sAdd('reg' , $chat_id);
}

function check_reg($chat_id)
{
 $redis = new Redis();
 $redis->connect('127.0.0.1');
 return $redis->sIsMember('reg',$chat_id);
 
}

function get_password()
{
 $redis = new Redis();
 $redis->connect('127.0.0.1');
 return $redis->get('password');
}


function sentsina($chat_id,$message)
{
 global $array;
 $bot_api_key =$array['bot_api_key']; 
 $bot_username=$array['bot_username'];


$telegram = new Telegram($bot_api_key , $bot_username);

// Get the chat id and message text from the CLI parameters.
//$chat_id = isset($argv[1]) ? $argv[1] : '';
//$message = isset($argv[2]) ? $argv[2] : '';

if ($chat_id !== '' && $message !== '') {
    $data = [
        'chat_id' => $chat_id,
        'text'    => $message,
    ];

    $result = Request::sendMessage($data);

    if ($result->isOk()) {
        echo 'Message sent succesfully to: ' . $chat_id;
    } else {
        echo 'Sorry message not sent to: ' . $chat_id;
    }
}

}

?>
