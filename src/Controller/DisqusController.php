<?php

namespace Drupal\disqus\Controller;

class DisqusController {
  /**
   * Menu callback; Automatically closes the window after the user logs in.
   *
   * @return
   *   A render array containing the confirmation message and link that closes overlay window.
   */
  public function closeWindow() {
     $build = array(
       '#markup'=> t('Thank you for logging in. Please close this window, or <a href="!clickhere">click here</a> to continue.', array('!clickhere' => 'javascript:window.close();')),
       '#attached' => array(
         'js' => array(
           array(
             'type' => 'inline',
             'data' => 'window.close();',
           ),
         ),
       ),
     );
     return $build;
  }
}
