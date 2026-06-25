<?php
namespace Drupal\mediaamp\Plugin\video_embed_field\Provider;
use Drupal\video_embed_field\ProviderPluginBase;
/**
 * MediaAmp provider plugin.
 *
 * @VideoEmbedProvider(
 *   id = "mediaamp",
 *   title = @Translation("MediaAmp")
 * )
 */
class MediaAmpProvider extends ProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function renderEmbedCode($width, $height, $autoplay) {
    $embed_code = [
      '#type' => 'video_embed_iframe',
      '#provider' => 'mediaamp',
      // '#url' => sprintf('//assets.ngeo.com/modules-video/latest/assets/ngsEmbeddedVideo.html'),
      '#url' => sprintf('https://player.mediaamp.io/p/U8-EDC/qQivF4esrENw/embed/select/media/%s',$this->getVideoId()),
      '#attributes' => [
        'width' => $width,
        'height' => $height,
        'frameborder' => '0',
        'allowfullscreen' => 'allowfullscreen',
      ],
    ];
    return $embed_code;
  }
 
  /**
   * {@inheritdoc}
   */
  public function getRemoteThumbnailUrl() {
    $url = 'http://img.mediaamp.io/vi/%s/%s.jpg';
    // $url = 'http://img.natgeo.com/vi/%s/%s.jpg';
    $high_resolution = sprintf($url, $this->getVideoId(), 'maxresdefault');
    $backup = sprintf($url, $this->getVideoId(), 'mqdefault');
    try {
      $this->httpClient->head($high_resolution);
      return $high_resolution;
    }
    catch (\Exception $e) {
      return $backup;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getIdFromInput($input) {
    preg_match('/^https?:\/\/(player\.mediaamp\.io)\/p\/(?<id2>[0-9A-Za-z_-]*)\/(?<id3>[0-9A-Za-z_-]*)\/embed\/select\/media\/(?<guid>[0-9A-Za-z_-]*)/', $input, $matches);

    return isset($matches['guid']) ? $matches['guid'] : FALSE;
  }

}