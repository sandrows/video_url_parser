<?php

namespace Drupal\video_url_parser;

use SimpleXMLElement;

class VideoUrlParser {

  private $service;

  /**
   * Class constructor
   *
   * @throws \Exception
   */
  public function __construct() {
    if (!extension_loaded('pcre')) {
      throw new \Exception("PCRE extension is required.");
    }
  }

  /**
   * Main function to start parsing based on the URL.
   * @param string $url
   *
   * @return array|null
   */
  public function parse($url) {
    $this->service = $this->identify_service($url);
    $video = NULL;

    if (!is_null($this->service)) {
      switch ($this->service) {
        case 'youtube':
          $id = $this->get_youtube_id($url);
          $video['type'] = 'youtube';
          $video['embed'] = $this->get_youtube_embed($id);
          break;

        case 'vimeo':
          $x = $this->vimeo_api($url);
          if ($x){
            $id = $x->video_id;
            $video['type'] = 'vimeo';
            $video['embed'] = $this->get_vimeo_embed($id);
          }
          break;
      }
    }

    return $video;
  }

  /**
   * Match the URL with regular expressions to determine the hoster.
   * @param string $url
   *
   * @return null|string
   */
  private function identify_service($url) {
    if (preg_match('%(?:https?:)?//(?:(?:www|m)\.)?(youtube(?:-nocookie)?\.com|youtu\.be)\/%i', $url)) {
      return 'youtube';
    }
    elseif (preg_match('%(?:https?:)?//(?:[a-z]+\.)*vimeo\.com\/%i', $url)) {
      return 'vimeo';
    }

    return NULL;
  }

  /**
   * Get YouTube's video ID.
   * @param string $url
   *
   * @return string
   */
  private function get_youtube_id($url) {
    $url_id_keys = ['v', 'vi'];
    $yid = $this->parse_url_params($url, $url_id_keys);
    if ($yid) {return $yid;}

    // Fallback for thelast parameter
    return $this->url_last_param($url);
  }

  /**
   * Construct YouTube's embed URL.
   * @param string $youtube_video_id
   * @param int $autoplay
   *
   * @return string
   */
  private function get_youtube_embed($youtube_video_id, $autoplay = 1) {
    $embed = "http://youtube.com/embed/$youtube_video_id?autoplay=$autoplay";
    return $embed;
  }

  /**
   * Construct Vimeo's embed URL.
   * @param string $vimeo_video_id
   * @param int $autoplay
   *
   * @return string
   */
  private function get_vimeo_embed($vimeo_video_id, $autoplay = 1) {
    $embed = "http://player.vimeo.com/video/$vimeo_video_id?autoplay=$autoplay";
    return $embed;
  }

  /**
   * Use Vimeo's API to get the ID and the thumbnail image.
   * @param string $url
   *
   * @return \SimpleXMLElement|false
   */
  private function vimeo_api($url) {
    $oembed_endpoint = 'http://vimeo.com/api/oembed';
    $video_url = $url;
    $xml_url = $oembed_endpoint . '.xml?url=' . rawurlencode($video_url) . '&width=640';

    try{
      $result = new SimpleXMLElement($xml_url, 0, TRUE);
    }catch (\Exception $ex){
      $result = FALSE;
    }

    return $result;
  }

  /**
   * Match the URL's query string against a targeted key.
   * @param string $url
   * @param array $target_params
   *
   * @return string|false
   */
  private function parse_url_params($url, $target_params) {
    parse_str(parse_url($url, PHP_URL_QUERY), $parsed_params);
    foreach ($target_params as $target) {
      if (array_key_exists($target, $parsed_params)) {
        return $parsed_params[$target];
      }
    }
    return FALSE;
  }

  /**
   * Get the last parameter's value of the URL.
   * @param string $url
   *
   * @return string
   */
  private function url_last_param($url) {
    $url_parts = explode("/", $url);
    $prospect = end($url_parts);
    $params = preg_split("/(\?|\=|\&)/", $prospect);

    if ($params) return $params[0];
    else return $prospect;
  }

}