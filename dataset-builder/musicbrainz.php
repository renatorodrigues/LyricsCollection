<?php
require_once('api_calls.php');

function getArtistsMBIds($artists, $pdo) {
  Log::d('MusicBrainz', 'Getting mbid\'s.');
  $new_artists = array();

  foreach ($artists as $artist) {
    $data = api_call('http://musicbrainz.org/ws/2/artist/', array('query' => '"' . $artist . '"'));
    $data = new SimpleXmlElement($data);
    
    $artist_list = $data->{'artist-list'};
    
    $artist_node;
    if ((int) $artist_list->attributes()->count !== 0) {
      $artist_node = $artist_list->artist;
      $new_artists[$artist] = (string) $artist_node->attributes()->id;
    }

    sleep(1);
  }

  Log::d('MusicBrainz', 'Found a total of ' . count($new_artists) . ' mbid\'s from ' . count($artists) . ' given artists');

  $sth_new_artist = $pdo->prepare('INSERT INTO Artists VALUES (:mbid, :name)');
  foreach ($new_artists as $name => $mb_id) {
    $sth_new_artist->bindParam('mbid', $mb_id);
    $sth_new_artist->bindParam('name', $name);
    $sth_new_artist->execute();
  }

  return $new_artists;
}

function getArtistRecordings($mb_id) {
  $limit = 100;
  $offset = 0;
  $recordings = array();
  $count = 100;
  
  $data = api_call('http://musicbrainz.org/ws/2/recording', array('artist' => $mb_id, 'limit' => $limit));
  $data = new SimpleXmlElement($data);
  $recording_list = $data->{'recording-list'};
  $count = (int) $recording_list->attributes()->count;

  foreach ($recording_list->recording as $recording) {
      $song_title = (string) $recording->title;
      if(!isset($recording[$song_title]))
        $recordings[$song_title] = (string) $recording->attributes()->id;
  }
  
  $offset += $limit;

  while($offset < $count) {
    $data = api_call('http://musicbrainz.org/ws/2/recording', array('artist' => $mb_id, 'limit' => $limit, "offset" => $offset));
    $data = new SimpleXmlElement($data);
    $recording_list = $data->{'recording-list'};

    foreach ($recording_list->recording as $recording) {
      $song_title = (string) $recording->title;
      if(!isset($recording[$song_title]))
        $recordings[$song_title] = (string) $recording->attributes()->id;
    }

    $offset += $limit;
  }

  return $recordings;
}

function getArtistsRecordings($artists_mb_ids, $pdo) {
  $sth_new_song = $pdo->prepare('INSERT INTO Songs (mb_id, title, artist_id) VALUES (:mbid, :title, :artist_id)');
  $recordings = array();
  foreach ($artists_mb_ids as $artist_name => $mb_id) {
    Log::d('MusicBrainz', 'Getting artist recordings for ' . $artist_name . ' with mb_id = ' . $mb_id);
    $recordings[$artist_name] = array('mbid' => $mb_id,'recordings' => getArtistRecordings($mb_id));
  
    foreach($recordings[$artist_name]['recordings'] as $song_title => $song_mb_id) {
      $sth_new_song->bindParam('mbid', $song_mb_id);
      $sth_new_song->bindParam('title', $song_title);
      $sth_new_song->bindParam('artist_id', $mb_id);
      try{
        $sth_new_song->execute();
      }
      catch(PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
          // duplicate entry, do something else
          Log::d('MusicBrainz', 'Duplicated key for song ' . $song_title . ' with mb_id ' . $song_mb_id . ' artist: ' . $artist_name . ' with mb_id = ' . $mb_id);
        }
        else {
          // an error other than duplicate entry occurred
          Log::d('MusicBrainz', 'Exception for song ' . $song_title . ' with mb_id ' . $song_mb_id . ' artist: ' . $artist_name . ' with mb_id = ' . $mb_id);
        }
      } 
    }
    
    sleep(1);
  }

  return $recordings;
}
?>