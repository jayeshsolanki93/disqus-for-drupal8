<?php

namespace Drupal\disqus\Controller;

use Drupal\Core\Controller\ControllerInterface;

class DisqusController implements ControllerInterface {
  /**
   * Menu callback; Automatically closes the window after the user logs in.
   *
   * @return
   *   Confirmation message and link that closes overlay window.
   */
  public function closeWindow() {
    drupal_add_js('window.close();', 'inline');
    return t('Thank you for logging in. Please close this window, or <a href="@clickhere">click here</a> to continue.', array('@clickhere' => 'javascript:window.close();'));
  }
}
