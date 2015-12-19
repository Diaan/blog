<?php

use Abraham\TwitterOAuth\TwitterOAuth;
use MetzWeb\Instagram\Instagram;		
 
class Processor {
 
    private $processor;
 
    function __construct() {
    	//$this->processor = $processor;
    }

    public function process($id,$type, $url) {
    		$url = preg_replace('./$', '', $url);
    	switch ($type) {
			case "twitter":
				$tweetid = explode("/", $url);
			    $post = $this->processTwitter($id,end($tweetid));
			    break;
			case "instagram":
				$instagramid = explode("/", $url);
			    $post = $this->processInstagram($id,end($instagramid));
			    break;
			case "flickr":
				$flickrid = explode("/", $url);
			    $post = $this->processFlickr($id,end($flickrid));
			    break;
			case "soundcloud":
			    $post = $this->processSoundcloud($id,$url);
			    break;
			case "youtube":
				$youtubeid = explode("/", $url);
			    $post = $this->processYoutube($id,end($youtubeid));
			    break;
			case "blogger":
			    $post = $this->processBlogger($id,$url);
			    break;
			default:
				$post = false;//$this->processNothing($id);
				$processed = 0;
		}
		if($post){
    		$db = new DbHandler();
    		$processed = $db->savePost($id, $post);
		}
		
		return $processed;
		//return $post;	    
    }

    public function info($id,$type, $url) {
    	$url = preg_replace('~/', '', $url);
    	switch ($type) {
			case "twitter":
				$tweetid = explode("/", $url);
			    $post = $this->getTwitterPost(end($tweetid));
			    break;
			case "instagram":
				$instagramid = explode("/", $url);
			    $post = $this->getInstagramPost(end($instagramid));
			    break;
			case "flickr":
				$flickrid = explode("/", $url);
			    $post = $this->getFlickrPost(end($flickrid));
			    break;
			case "soundcloud":
			    $post = $this->getSoundcloudPost($url);
			    break;
			case "youtube":
				$youtubeid = explode("/", $url);
			    $post = $this->getYoutubePost(end($youtubeid));
			    break;
			case "blogger":
				$post = $this->getBloggerPost($url);
			    break;
			default:
				$post = false;//$this->processNothing($id);
				$processed = 0;
		}
		
		return $post;	    
    }

    public function getTwitterPost($tweetid){
    	$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
    	$status = $connection->get("statuses/show", array("id" => $tweetid));
    	
    	return $status;
    }

    public function processTwitter($id,$tweetid){
    	$tweet = array();
    	$status = $this->getTwitterPost($tweetid);

    	if($status->geo!=null){
	    	$geo = $status->geo;
	    	$tweet["location"]  = array('lat' => $geo->coordinates[0],'long'=>$geo->coordinates[1]);
	    }
	    if(isset($status->extended_entities->media[0]->media_url)){
	    	$tweet["imageurl"] = $status->extended_entities->media[0]->media_url;
	    }
		
		$tweet["text"] = $status->text;

		$date = DateTime::createFromFormat('D F d G:i:s O Y', $status->created_at);
      	$timestamp = $date->format('Y-m-d H:i:s');
    	$tweet["timestamp"] = $timestamp;

    	return $tweet;

    }

    public function getInstagramPost($instagramid){
    	$instagram = new Instagram(INSTAGRAM_CONSUMER_KEY);
		$media = $instagram->getMedia("shortcode/".$instagramid)->data;
		
		return $media;
    }

    public function processInstagram($id,$instagramid){
		$photo = array();
    	$media = $this->getInstagramPost($instagramid);

		if($media->location!=null){
	    	$photo["location"]  = array('lat' => $media->location->latitude,'long'=>$media->location->longitude);
	    }
	    $photo["text"] = $media->caption->text;

	    $date = DateTime::createFromFormat('U', $media->created_time);
      	$timestamp = $date->format('Y-m-d H:i:s');
    	$photo["timestamp"] = $timestamp;
    	$photo["imageurl"] = $media->images->standard_resolution->url;
	    
    	return $photo;
    }

    public function getFlickrPost($flickrid){
		$url = "https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key=".FLICKR_API_KEY."&photo_id=".$flickrid."&format=json&nojsoncallback=1";
		$response = file_get_contents( $url );
		$photo = json_decode($response)->photo;

		$imgurl = "https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=".FLICKR_API_KEY."&photo_id=".$flickrid."&format=json&nojsoncallback=1";
		$imgresponse = file_get_contents( $imgurl );
		$photo->urls = json_decode($imgresponse)->sizes->size;

		return $photo;
    }

    public function processFlickr($id,$flickrid){
		$photo = array();
    	$media = $this->getFlickrPost($flickrid);

		if($media->location!=null){
	    	$photo["location"]  = array('lat' => $media->location->latitude,'long'=>$media->location->longitude);
	    }
	    $photo["title"] = $media->title->_content;
	    $photo["text"] = $media->description->_content;

	    $date = DateTime::createFromFormat('Y-m-d H:i:s', $media->dates->taken);
      	$timestamp = $date->format('Y-m-d H:i:s');
    	$photo["timestamp"] = $timestamp;
    	$photo["imageurl"] = $media->urls[7]->source;
	    
    	return $photo;
    }

    public function getSoundcloudPost($soundcloudUrl){
		$url = "http://api.soundcloud.com/resolve?url=".$soundcloudUrl."&client_id=".SOUNDCLOUD_API_KEY;
		$response = file_get_contents( $url );
		$sound = json_decode($response);

		$embedurl = "http://soundcloud.com/oembed?format=json&url=".$soundcloudUrl;
		$embedresponse = file_get_contents( $embedurl );
		$sound->embedcode = json_decode($embedresponse)->html;

		return $sound;
    }

    public function processSoundcloud($id,$soundcloudUrl){
		$sound = array();
    	$media = $this->getSoundcloudPost($soundcloudUrl);

	    $sound["text"] = $media->description;

	    $date = DateTime::createFromFormat('Y/m/d H:i:s e', $media->created_at);
      	$timestamp = $date->format('Y-m-d H:i:s');
    	$sound["timestamp"] = $timestamp;
    	
    	$sound["imageurl"] = $media->artwork_url;
	    $sound["title"] = $media->title;
	    $sound["embedcode"] = $media->embedcode;
	    
    	return $sound;
    }

    public function getYoutubePost($youtubeId){
		$url = "https://www.googleapis.com/youtube/v3/videos?part=snippet%2Cplayer%2CrecordingDetails&id=".$youtubeId."&key=".YOUTUBE_API_KEY;
		$response = file_get_contents( $url );
		$video = json_decode($response)->items[0];

		return $video;
    }

    public function processYoutube($id,$youtubeid){
		$video = array();
    	$media = $this->getYoutubePost($youtubeid);

	    $video["text"] = $media->snippet->description;
/*2015-11-15T15:08:52.000Z
	    $date = DateTime::createFromFormat('Y-m-dTH:i:s e', $media->snippet->publishedAt);
      	$timestamp = $date->format('Y-m-d H:i:s');
    	$video["timestamp"] = $timestamp;
*/    	
    	$video["imageurl"] = $media->snippet->thumbnails->high->url;
	    $video["title"] = $media->snippet->title;
	    $video["embedcode"] = $media->player->embedHtml;
	    
    	return $video;
    }

	public function getBloggerPost($bloggerUrl){
		$urlParts = explode("/", $bloggerUrl);
		$blogurl = "https://www.googleapis.com/blogger/v3/blogs/byurl?url=".$urlParts[0]."//".$urlParts[2]."&key=".YOUTUBE_API_KEY;
		$responseBlogurl = file_get_contents( $blogurl );
		$blogId = json_decode($responseBlogurl)->id;
		
		$postPath = join("/",array_slice ( $urlParts , 3));
		$url = "https://www.googleapis.com/blogger/v3/blogs/".$blogId."/posts/bypath?path=/".$postPath."&key=".YOUTUBE_API_KEY;
		$response = file_get_contents( $url );
		$blog = json_decode($response);

		return $blog;
    }

    public function processBlogger($id,$bloggerUrl){
		$blog = array();
    	$media = $this->getBloggerPost($bloggerUrl);

	    if($media->location!=null){
	    	$blog["location"]  = array('lat' => $media->location->lat,'long'=>$media->location->lng);
	    }
	    $blog["text"] = $media->content;
		//$media->published = "2015-10-27T06:46:00-07:00";//Z
	    $date = DateTime::createFromFormat('Y-m-d\TH:i:se', $media->published);//.u\Z
      	$timestamp = $date->format('Y-m-d H:i:s');
    	$blog["timestamp"] = $timestamp;
    	
	    $blog["title"] = $media->title;
	    
    	return $blog;
    }

    private function processNothing($id){
    	return "nothing, id:".$id;
    }
 }
 ?>