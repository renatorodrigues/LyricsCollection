<?php
function api_call($url, $params = null) {
  if($params !== null) {
    $url .= '?'. http_build_query($params);
  }

  $data = file_get_contents($url);
  return $data;
}
?>