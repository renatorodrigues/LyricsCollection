<?php
require_once('api_calls.php');

function getLyric($artist_term, $song_term) {

  $search_results = api_call("http://api.chartlyrics.com/apiv1.asmx/SearchLyric", array('artist'=>$artist_term,'song'=>$song_term));
  if ($search_results === false) {
    return null;
  }

  $search_results = new SimpleXmlElement($search_results);
  $search_results = $search_results->SearchLyricResult;

  foreach ($search_results as $result) {
    if (isset($result->attributes('xsi', true)->nil)) {
      return false;
    }

    $artist = (string) $result->Artist;
    if(strcasecmp($artist_term, $artist) !== 0) {
      //echo 'artist_term ' . $artist_term . ' different from artist ' . $artist . "\n";
      continue;
    }

    $song = $result->Song;
    if(strcasecmp($song_term, $song) !== 0) {
      //echo 'artist_term ' . $artist_term . ' different from artist ' . $artist . "\n";
      continue;
    }

    $lyric_id = (int) $result->LyricId;
    if ($lyric_id === 0) {
      //echo 'the song ' . $song_term . ' from ' . $artist_term . ' does not have a lyric' . "\n";
      return false;
    }


    $lyric_checksum = (string) $result->LyricChecksum;

    return array('lyricId' => $lyric_id, 'lyricChecksum' => $lyric_checksum);
  }
}

/*
* $params: array('lyricId' => 123, 'lyricChecksum' => 'asdasdasdasdasdadsdasdasd')
*/
function getLyricContent($params) {
  $data = api_call("http://api.chartlyrics.com/apiv1.asmx/GetLyric", $params);
  if ($data !== false) {
    $data = new SimpleXmlElement($data);
    return (string) $data->Lyric;
  } else {
    return false;
  }
}

function getArtistsLyrics(&$artists_recordings, $pdo) {
  Log::d('ChartLyrics', 'ChartLyrics initialized.');

  $sth_new_lyric = $pdo->prepare('INSERT INTO Lyrics VALUES (:id, :checksum, :content, :song_id)');
  
  foreach ($artists_recordings as $artist_name => &$recordings) {
    foreach ($recordings['recordings'] as $recording_name => &$recording) {
      $lyric = false;
      $params = null;
      while ($params === null) {
        $params = getLyric($artist_name, $recording_name);
        
        if ($params === null) {
          sleep(10);
        }
      }

      if ($params !== false) {
        $content = false;
        while ($content === false) {
          $content = getLyricContent($params);
          
          if ($content === false) {
            sleep(10);
          }
        }
        
        echo $content . "\n\n";
        array_merge($recording, array('lyric_id' => $params['lyricId'], 'lyric_checksum' => $params['lyricChecksum'], 'lyric_content' => $content));

        $sth_new_lyric->bindParam('id', $params['lyricId'], PDO::PARAM_INT);
        $sth_new_lyric->bindParam('checksum', $params['lyricChecksum']);
        $sth_new_lyric->bindParam('content', $content);
        $sth_new_lyric->bindParam('song_id', $recording['mbid']);
        $sth_new_lyric->execute();
      }
    }

  }

  Log::d('ChartLyrics', 'ChartLyrics initialized.');
}


?>