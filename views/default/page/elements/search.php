<?php
$site_name = elgg_get_site_entity()->name;

$htmlBody = <<<END
<a href="/videos/all" title="Watch videos on $site_name "> 
<img alt="Videos on '. $site_name . ' " height="36" src="/_graphics/elgg_logo.png" width="36" align="left"></a>                  
<img alt="Youtube Logo" height="36" src="/mod/videos/graphics/youtube.jpg" width="36" align="right"></a>
<br>
<form method="GET">
  <div>
    <b><center>Search Videos:</center></b><br> <input type="search" id="q" name="q" placeholder="Search Videos">
  </div>
  <div>
    Results (max 10): <input type="number" id="maxResults" name="maxResults" min="1" max="10" step="1" value="5">
    <br><br>
   </div>
  <input type="submit" value="Search" class="elgg-button elgg-button-submit">
  <br><br>
</form>
END;

if ($_GET['q'] && $_GET['maxResults']) {
  // Call set_include_path() as needed to point to your client library.

    require_once(elgg_get_plugins_path() . "videos/vendors/google/Google_Client.php");
    require_once(elgg_get_plugins_path() . "videos/vendors/google/contrib/Google_YouTubeService.php");

  /* Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
  Google APIs Console <http://code.google.com/apis/console#access>
  Please ensure that you have enabled the YouTube Data API for your project. */
  $DEVELOPER_KEY = elgg_get_plugin_setting('developer_key', 'videos');

  $client = new Google_Client();
  $client->setDeveloperKey($DEVELOPER_KEY);

  $youtube = new Google_YoutubeService($client);

  try {
    $searchResponse = $youtube->search->listSearch('id,snippet', array(
      'q' => $_GET['q'],
      'maxResults' => $_GET['maxResults'],
	  'safeSearch' => 'strict',
    ));

    $videos = '';
    $channels = '';
    $playlists = '';

    foreach ($searchResponse['items'] as $searchResult) {
	
	$title = $searchResult['snippet']['title'];	
	$title = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9\_|+.-]/', ' ', urldecode(html_entity_decode(strip_tags($title))))));
	$desc = $searchResult['snippet']['description'];
	$desc = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9\_|+.-]/', ' ', urldecode(html_entity_decode(strip_tags($desc))))));
	$video_url = "https://www.youtube.com/watch?v=" . $searchResult['id']['videoId'];	
	
	
	if(elgg_is_logged_in()) {
	
      switch ($searchResult['id']['kind']) {
      case 'youtube#video':
	  $videos .= "</br><a href='/videos/add/$user->guid?title=$title&desc=$desc&video_url=$video_url'>Add this video:</a> ". $searchResult['snippet']['title'];
      $videos .= "</br>";
	  $videos .=  "<iframe width='200' height='150' src='https://www.youtube.com/embed/";
      $videos .= $searchResult['id']['videoId'];  
	  $videos .= "' frameborder='0'></iframe>";
	  $videos .= "</br>";
	  $videos .= $searchResult['snippet']['title'];
	  $videos .= "</a></br>";
	  
      break;
      }
    }else{
	
	  switch ($searchResult['id']['kind']) {
      case 'youtube#video':
	  $videos .= "</br>";
	  $videos .= $searchResult['snippet']['title'];
	  $videos .=  "<iframe width='200' height='150' src='https://www.youtube.com/embed/";
      $videos .= $searchResult['id']['videoId'];  
	  $videos .= "' frameborder='0'></iframe>";
	  $videos .= "</a></br>";
      break;
      }
	  }
	  }
	  

    $htmlBody .= <<<END
    <h3>Youtube Videos</h3>
    <ul>$videos</ul>
   _____________________________ 
END;
  } catch (Google_ServiceException $e) {
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  }
}
?>

<!doctype html>
<html>
    <?=$htmlBody?>
</html>

