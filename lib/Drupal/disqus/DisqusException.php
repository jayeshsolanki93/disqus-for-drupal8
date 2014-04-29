<?php

/**
 * @file
 * Provide Disqus Exception.
 */

namespace Drupal\disqus;

/**
 * Any unsucessful result that's created by Disqus API will generate a DisqusException.
 */
class DisqusException extends Exception {
  /**
   * The information returned from the cURL call.
   */
  public $info = NULL;

  /**
   * The information returned from the Disqus call.
   */
  public $disqus = NULL;

  /**
   * Creates a DisqusException.
   * @param $message
   *   The message for the exception.
   * @param $code
   *   (optional) The error code.
   * @param $info
   *   (optional) The result from the cURL call.
   */
  public function __construct($message, $code = 0, $info = NULL, $disqus = NULL) {
    $this->info = $info;
    $this->disqus = $disqus;
    parent::__construct($message, $code);
  }

  /**
   * Converts the exception to a string.
   */
  public function __toString() {
    $code = isset($this->disqus->code) ? $this->disqus->code : (isset($info['http_code']) ? $info['http_code'] : 0);
    $message = $this->getMessage();
    return __CLASS__ .": [$code]: $message\n";
  }
}
