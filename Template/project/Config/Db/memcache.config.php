<?
/**
 * 数据缓存
 * memcached服务器连接信息
 */
$ip       = '127.0.0.1';
$hosts[]  = array('ip' => $ip, 'port' => '11211');  //服务器一
$interval = 300;                                    //连接出错时重试间隔时间(秒);
?>