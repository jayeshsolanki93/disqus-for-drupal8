<?php

/**
 * @file
 * Contains \Drupal\disqus\Form\DisqusSettingsForm.
 */

namespace Drupal\disqus\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DisqusSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'disqus_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $disqus_config = $this->config('disqus.settings');

    $form['disqus_domain'] = array(
      '#type' => 'textfield',
      '#title' => t('Shortname'),
      '#description' => t('The website shortname that you registered Disqus with. If you registered http://example.disqus.com, you would enter "example" here.'),
      '#default_value' => $disqus_config->get('disqus_domain'),
    );
    $form['settings'] = array(
      '#type' => 'vertical_tabs',
      '#attached' => array(
        'js' => array(
          drupal_get_path('module', 'disqus') . '/disqus.settings.js'
        ),
      ),
      '#weight' => 50,
    );
    // Visibility settings.
    $form['visibility'] = array(
      '#type' => 'details',
      '#title' => t('Visibility'),
      '#group' => 'settings',
    );
    $types = node_type_get_types();
    $options = array();
    foreach ($types as $type) {
      $options[$type->type] = $type->name;
    }
    $node_types = $disqus_config->get('visibility.disqus_nodetypes');
    $form['visibility']['disqus_nodetypes'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Node Types'),
      '#description' => t('Apply comments to only the following node types.'),
      '#default_value' => !empty($node_types) ? $node_types : array(),
      '#options' => $options,
    );
    $form['visibility']['disqus_location'] = array(
      '#type' => 'select',
      '#title' => t('Location'),
      '#description' => t('Display the Disqus comments in the given location. When "Block" is selected, the comments will appear in the <a href="@disquscomments">Disqus Comments block</a>.', array('@disquscomments' => url('admin/structure/block'))),
      '#default_value' => $disqus_config->get('visibility.disqus_location'),
      '#options' => array(
        'content_area' => t('Content Area'),
        'block' => t('Block'),
      ),
    );
    $form['visibility']['disqus_weight'] = array(
      '#type' => 'select',
      '#title' => t('Weight'),
      '#description' => t('When the comments are displayed in the content area, you can change the position at which they will be shown.'),
      '#default_value' => $disqus_config->get('visibility.disqus_weight'),
      '#options' => array(
        '-100' => '-100',
        '-75' => '-75',
        '-50' => '-50',
        '-25' => '-25',
        '0' => '0',
        '25' => '25',
        '50' => '50',
        '75' => '75',
        '100' => '100',
      ),
      '#states' => array(
        'visible' => array(
          'select[name="disqus_location"]' => array('value' => 'content_area'),
        ),
      ),
    );
    // Behavior settings.
    $form['behavior'] = array(
      '#type' => 'details',
      '#title' => t('Behavior'),
      '#group' => 'settings',
    );
    $form['behavior']['disqus_localization'] = array(
      '#type' => 'checkbox',
      '#title' => t('Localization support'),
      '#description' => t("When enabled, overrides the language set by Disqus with the language provided by the site."),
      '#default_value' => $disqus_config->get('behavior.disqus_localization'),
    );
    $form['behavior']['disqus_inherit_login'] = array(
      '#type' => 'checkbox',
      '#title' => t('Inherit User Credentials'),
      '#description' => t("When enabled and a user is logged in, the Disqus 'Post as Guest' login form will be pre-filled with the user's name and email address."),
      '#default_value' => $disqus_config->get('behavior.disqus_inherit_login'),
    );
    $form['behavior']['disqus_developer'] = array(
      '#type' => 'checkbox',
      '#title' => t('Testing'),
      '#description' => t('When enabled, uses the <a href="http://docs.disqus.com/help/2/">disqus_developer</a> flag to tell Disqus that you are in a testing environment. Threads will not display on the public community page with this set.'),
      '#default_value' => $disqus_config->get('behavior.disqus_developer'),
    );
    // Advanced settings.
    $form['advanced'] = array(
      '#type' => 'details',
      '#title' => t('Advanced'),
      '#group' => 'settings',
      '#description' => t('Use these settings to configure the more advanced uses of Disqus. You can find more information about these in the <a href="@applications">Applications</a> section of Disqus. To enable some of these features, you will require a <a href="@addons">Disqus Add-on Package</a>.', array(
        '@applications' => 'http://disqus.com/api/applications/',
        '@addons' => 'http://disqus.com/addons/',
      )),
    );
    $form['advanced']['disqus_useraccesstoken'] = array(
    '#type' => 'textfield',
    '#title' => t('User Access Token'),
    '#default_value' => $disqus_config->get('advanced.disqus_useraccesstoken'),
    );
    $form['advanced']['disqus_publickey'] = array(
      '#type' => 'textfield',
      '#title' => t('Public Key'),
      '#default_value' => $disqus_config->get('advanced.disqus_publickey'),
    );
    $form['advanced']['disqus_secretkey'] = array(
      '#type' => 'textfield',
      '#title' => t('Secret Key'),
      '#default_value' => $disqus_config->get('advanced.disqus_secretkey'),
    );
    $form['advanced']['sso'] = array(
      '#weight' => 5,
      '#type' => 'fieldset',
      '#title' => t('Single Sign-on'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#states' => array(
        'visible' => array(
          'input[name="disqus_publickey"]' => array('empty' => FALSE),
          'input[name="disqus_secretkey"]' => array('empty' => FALSE),
        ),
      ),
    );
    $form['advanced']['sso']['disqus_sso'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Single Sign-On'),
      '#description' => t('Provide <a href="@sso">Single Sign-On</a> access to your site.', array(
        '@sso' => 'http://disqus.com/api/sso/',
      )),
      '#default_value' => $disqus_config->get('advanced.sso.disqus_sso'),
    );
    $form['advanced']['sso']['disqus_use_site_logo'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Site Logo'),
      '#description' => t('Pass the site logo to Disqus for use as SSO login button.'),
      '#default_value' => $disqus_config->get('advanced.sso.disqus_use_site_logo'),
      '#states' => array(
        'disabled' => array(
          'input[name="disqus_sso"]' => array('checked' => FALSE),
        ),
      ),
    );
    $form['advanced']['sso']['disqus_logo'] = array(
      '#type' => 'managed_file',
      '#title' => t('Custom Logo'),
      '#upload_location' => 'public://images',
      '#default_value' => $disqus_config->get('advanced.sso.disqus_logo'),
      '#states' => array(
        'disabled' => array(
          'input[name="disqus_sso"]' => array('checked' => FALSE),
        ),
        'visible' => array(
          'input[name="disqus_use_site_logo"]' => array('checked' => FALSE),
        ),
      ),
    );
//    $form['actions'] = array('#type' => 'actions');
//    $form['actions']['submit'] = array(
//      '#type' => 'submit',
//      '#value' => $this->t('Add'),
//    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $config = $this->config('disqus.settings');
    $config
      ->set('disqus_domain', $form_state['values']['disqus_domain'])
      ->set('visibility.disqus_nodetypes', $form_state['values']['disqus_nodetypes'])
      ->set('visibility.disqus_location', $form_state['values']['disqus_location'])
      ->set('visibility.disqus_weight', $form_state['values']['disqus_weight'])
      ->set('behavior.disqus_localization', $form_state['values']['disqus_localization'])
      ->set('behavior.disqus_inherit_login', $form_state['values']['disqus_inherit_login'])
      ->set('behavior.disqus_developer', $form_state['values']['disqus_developer'])
      ->set('advanced.disqus_useraccesstoken', $form_state['values']['disqus_useraccesstoken'])
      ->set('advanced.disqus_publickey', $form_state['values']['disqus_publickey'])
      ->set('advanced.disqus_secretkey', $form_state['values']['disqus_secretkey'])
      ->set('advanced.sso.disqus_sso', $form_state['values']['disqus_sso'])
      ->set('advanced.sso.disqus_use_site_logo', $form_state['values']['disqus_use_site_logo'])
      ->set('advanced.sso.disqus_logo', $form_state['values']['disqus_logo'])
      ->save();

    parent::submitForm($form, $form_state);

    // $old_logo = variable_get('disqus_logo', '');
    // $new_logo = (isset($form_state['values']['disqus_logo'])) ? $form_state['values']['disqus_logo'] : '';
    // // Ignore if the file hasn't changed.
    // if ($new_logo != $old_logo) {
    //   // Remove the old file and usage if previously set.
    //   if ($old_logo != '') {
    //     $file = file_load($old_logo);
    //     file_usage_delete($file, 'disqus', 'disqus');
    //     file_delete($file);
    //   }
    //   // Update the new file and usage.
    //   if ($new_logo != '') {
    //     $file = file_load($new_logo);
    //     file_usage_add($file, 'disqus', 'disqus', 0);
    //     $file->status = FILE_STATUS_PERMANENT;
    //     file_save($file);
    //   }
    // }
  }
}
