README for Disqus for Drupal 8

Disqus 8.x-1.x
=================================

Disqus Official PHP API Support
=================================

INSTALL
=============
1. You will need to install the Composer Manager module.

   https://www.drupal.org/project/composer_manager

   Make sure you have drush installed (Drush is a command-line shell and scripting
   interface for Drupal).
   Read the installation instructions for installing drush here:

   https://github.com/drush-ops/drush

2. Obtain your user access key from the application specific page found here:

   http://disqus.com/api/applications/

3. Now run the following commands from within your Drupal root directory to download 
   the disqusapi bindings:

   $ drush composer-json-rebuild

   $ drush composer-manager install

BUILT-IN FEATURES
=============
This module can automatically update and/or delete your Disqus threads when you
delete/update the entities for which disqus field is enabled.

Visit Disqus configuration page (admin/config/services/disqus) after you have
installed Disqus API to configure it's behaviour.

EXAMPLES
=============
You can find the API reference here:

http://disqus.com/api/docs/

Any of these methods can be called by creating an instance of the Disqus API
through disqus_api(). You must use try/catch to avoid php throwing a general
exception and stopping script execution.

For a full explanation of the official API you can view the readme located here:

https://github.com/disqus/disqus-php/blob/master/README.rst

Example: Calling threads/details and threads/update

  $disqus = disqus_api();
  if ($disqus) {
    try {
      // Load the thread data from disqus. Passing thread is required to allow the thread:ident call to work correctly. There is a pull request to fix this issue.
      $thread = $disqus->threads->details(array('forum' => $config->get('disqus_domain'), 'thread:ident' => "{$entity->getEntityTypeId()}/{$entity->id()}", 'thread' => '1'));
    }
    catch (Exception $exception) {
      drupal_set_message(t('There was an error loading the thread details from Disqus.'), 'error');
      \Drupal::logger('disqus')->error('Error loading thread details for entity : !identifier. Check your API keys.', array('!identifier' => "{$entity->getEntityTypeId()}/{$entity->id()}"));
    }
    if (isset($thread->id)) {
      try {
        $disqus->threads->update(array('access_token' => $config->get('advanced.disqus_useraccesstoken'), 'thread' => $thread->id, 'forum' => $config->get('disqus_domain'), 'title' => $entity->label(), 'url' => $entity->url('canonical',array('absolute' => TRUE))));
      }
      catch (Exception $exception) {
        drupal_set_message(t('There was an error updating the thread details on Disqus.'), 'error');
        \Drupal::logger('disqus')->error('Error updating thread details for entity : !identifier. Check your user access token.', array('!identifier' => "{$entity->getEntityTypeId()}/{$entity->id()}"));
      }
    }
  }

