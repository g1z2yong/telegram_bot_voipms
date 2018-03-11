<?PHP
require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

include "function.php";

if(isset($_GET['to']) && isset($_GET['from']) && isset($_GET['message']))
{
   $body=sprintf("%s\nFrom: /sms %s\nto:%s\n",$_GET['message'],$_GET['from'],$_GET['to']);   echo $body;
  $chat_id=get_chat_id($_GET['to']);
   sentsina($chat_id,$body);
   savesms($_GET['from'],$_GET['to'],$_GET['message']);
}


?>
