<?php
function api_call($url, $params = null) {
  if($params !== null) {
      $url .= '?'. http_build_query($params);
  }

  $context = stream_context_create(array(
          'http' => array(
              'ignore_errors' => true
          )
      )
  );

  $data = file_get_contents($url, FALSE, $context);
  return $data;
}
?>