<?php
require_once('lib/simplehtmldom/simple_html_dom.php');

function parseArtists($html, &$artists, $offset) {
  Log::d('LastFM', 'Scraping artists from page with offset=' . $offset);
  foreach ($html->find('h3[class="remove-vertical-margins"]') as $e) {
    $artists[$offset--] = $e->innertext;
  }
}

function scrapingLastFM() {
  Log::d('LastFM', 'Scraping initialized.');
  
  $url = 'http://www.last.fm/bestof/10years/artists';

  $html = file_get_html($url);

  // Get pagination
  Log::d('LastFM', 'Getting pagination.');

  $from = array();
  foreach ($html->find('nav[class="ten-years-pagination"] ul', 0)->find('li') as $e) {
    $from_to = explode('&thinsp;-&thinsp;', $e->plaintext);
    $from[] = $from_to[0];
  }
  $artists_count = $from[0];

  Log::d('LastFM', 'Pages successfully acquired.');

  $artists = array();

  // Get artists for each page
  Log::d('LastFM', 'Getting artists for each page.');

  parseArtists($html, $artists, $from[0]); // Current page (first)

  // Fetch remaining pages and parse its artists
  for ($i = 1; $i < count($from); ++$i) {
    $url_with_offset = $url . '?from=' . $from[$i];
    $html = file_get_html($url_with_offset);
    parseArtists($html, $artists, $from[$i]);
  }

  $html->clear();
  unset($html);

  Log::d('LastFM', 'Scraping finished.');

  return  $artists;
}
?>