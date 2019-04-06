<?php

  // Интеграция CRM Clientbase с АТС Астериск (VPBX Asterisk)
  // https://ClientbasePro.ru
  // http://asterisk.ru/
  
require_once 'common.php'; 


    // функция возвращает массив событий из астериска CoreShowChannels по внутр.номеру $internal
function GetCoreShowChannels($internal='') {        
  if (!$internal) return false;
    // открываем соединение к астериск
  $socket = fsockopen(ASTERISK_HOST, 5038, $errno, $errstr);
  if (!$socket) exit('socket open error: '.$errstr.' ('.$errno.')');
  else {
    fputs($socket, "Action: Login\r\n"); 
    fputs($socket, "UserName: ".ASTERISK_AMI_USER."\r\n");
    fputs($socket, "Secret: ".ASTERISK_AMI_PASSWORD."\r\n\r\n"); 
    fputs($socket, "Action: CoreShowChannels\r\n"); 
    fputs($socket, "Action: Logoff\r\n\r\n");
    while ('Event: CoreShowChannelsComplete'!=$row) {
      $row = preg_replace("/[\t\r\n\v]+/",'',fgets($socket));
        // в массив $event добавляем только события CoreShowChannel
      if ('Event: CoreShowChannel'==$row) {
          // если массив $b уже заполнен, записываем его в промежуточный массив событий CoreShowChannel $events и затем очищаем
        if ($event) { $events[] = $event; $event = []; }
          // если это первое событие CoreShowChannel, включаем сбор массива $event 
        $use = 1;
      }
        // сбор массива $event
      if ($use) { $tmp = explode(':',$row); if (($t0=trim($tmp[0])) && ($t1=trim($tmp[1]))) $event[$t0] = $t1; }
        // для защиты от незавершённого $socket
      if (10000<$i++) break;
    }
  }     
  fclose ($socket);
    // проходим по массиву $events и удаляем те элементы, в которых ConnectedLineNum и CallerIDNum не соответствует $internal
  foreach ($events as $index=>$event) if ($internal!=$event['ConnectedLineNum'] && $internal!=$event['CallerIDNum']) unset($events[$index]);
  return $events;  
}




?>