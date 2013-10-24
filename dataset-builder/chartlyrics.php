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
    
    $content['lyric_content'] = (string) $data->Lyric;
    $content['lyric_url'] = (string) $data->LyricUrl;
    return $content;
  }
  else {
    return false;
  }
}

function getArtistsLyrics(&$artists_recordings, $pdo) {
  Log::d('ChartLyrics', 'ChartLyrics initialized.');

  $sth_new_lyric = $pdo->prepare('INSERT INTO Lyrics VALUES (:id, :checksum, :content, :song_id, :url)');
  
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
        array_merge($recording, array('lyric_id' => $params['lyricId'], 
                                      'lyric_checksum' => $params['lyricChecksum'],
                                      'lyric_content' => $content['lyric_content'],
                                      'lyric_url' => $content['lyric_url']));

        $sth_new_lyric->bindParam('id', $params['lyricId'], PDO::PARAM_INT);
        $sth_new_lyric->bindParam('checksum', $params['lyricChecksum']);
        $sth_new_lyric->bindParam('content', $content['lyric_url']);
        $sth_new_lyric->bindParam('song_id', $recording['mbid']);
        $sth_new_lyric->bindParam('url', $recording['lyric_url']);
        $sth_new_lyric->execute();
      }
    }

  }

  Log::d('ChartLyrics', 'ChartLyrics initialized.');
}

function getArtistsLyricsFromDB($pdo) {
  Log::d('ChartLyrics', 'ChartLyrics initialized.');

  $stmt_songs = $pdo->prepare('SELECT Songs.mb_id AS song_mb_id, title, name FROM Songs, Artists
                                  WHERE artist_id = Artists.mb_id AND checked = 0');

  $stmt_songs->execute();
  $results = $stmt_songs->fetchAll();
  Log::d('ChartLyrics', 'Fetched songs: ' . count($results));
  /*print_r($results);
  die();*/

  $sth_new_lyric = $pdo->prepare('INSERT INTO Lyrics VALUES (:id, :checksum, :content, :url)');
  $sth_update_song = $pdo->prepare('UPDATE Songs SET lyric_id = :lyric_id, checked = 1 WHERE mb_id = :mb_id');
  $sth_update_song_checked = $pdo->prepare('UPDATE Songs SET checked = 1 WHERE mb_id = :mb_id');
  $sth_lyric_exists = $pdo->prepare('SELECT id FROM LYRICS WHERE id = :id');
  
  foreach ($results as $song) {
    $lyric = false;
    $params = null;
    while ($params === null) {
      $params = getLyric($song['name'], $song['title']);
      
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

      $sth_new_lyric->bindParam('id', $params['lyricId'], PDO::PARAM_INT);
      $sth_new_lyric->bindParam('checksum', $params['lyricChecksum']);
      $sth_new_lyric->bindParam('content', $content['lyric_content']);
      $sth_new_lyric->bindParam('url', $content['lyric_url']);

      $sth_update_song->bindParam('lyric_id', $params['lyricId']);
      $sth_update_song->bindParam('mb_id', $song['song_mb_id']);

      //chek if lyric already exists
      $sth_lyric_exists->bindParam('id', $params['lyricId']);
      $sth_lyric_exists->execute();
      $exists = $sth_lyric_exists->fetchAll();
      
      if(count($exists) > 0) {
        $sth_update_song->execute();
      }
      else {
        $pdo->beginTransaction();
        $sth_new_lyric->execute();
        $sth_update_song->execute();

        $pdo->commit();

      }
    }
    else {
      $sth_update_song_checked->bindParam('mb_id', $song['song_mb_id']);
      $sth_update_song_checked->execute();
    }
  }

  Log::d('ChartLyrics', 'ChartLyrics finished.');
}


?>