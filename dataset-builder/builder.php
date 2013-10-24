<?php
set_time_limit(0);

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('config.php');
require_once('log.php');

require_once('last_fm.php');
require_once('musicbrainz.php');
require_once('chartlyrics.php');

//$verbose = isset($_GET['verbose']) && $_GET['verbose'] === 'true';
$verbose = true;
Log::setEnabled($verbose);

// LastFM
$artists = scrapingLastFM();
//$artists = array('The Shins', 'My Chemical Romance', 'Queens of the Stone Age', 'Ke$ha');

// MusicBrainz
Log::d('MusicBrainz', 'MusicBrainz initialized.');
$artists_mb_ids = getArtistsMBIds($artists, $pdo);
$artists_recordings = getArtistsRecordings($artists_mb_ids, $pdo);
Log::d('MusicBrainz', 'MusicBrainz finished.');

// ChartLyrics
$artists_lyrics = getArtistsLyrics($artists_recordings, $pdo);

?>