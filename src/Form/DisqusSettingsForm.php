<?php

/**
 * @file
 * Contains \Drupal\disqus\Form\DisqusSettingsForm.
 */

namespace Drupal\disqus\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use Drupal\Core\Form\FormStateInterface;

class DisqusSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * A database backend file usage overridable.
   *
   * @var \Drupal\file\FileUsage\DatabaseFileUsageBackend
   */
  protected $fileUsage;

  /**
   * Constructs a \Drupal\disqus\DisqusSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandler $module_handler, DataBaseFileUsageBackend $file_usage) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->fileUsage = $file_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('file.usage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'disqus_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['disqus.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
        'library' => array(
          'disqus/disqus.settings'
        ),
      ),
      '#weight' => 50,
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
    $form['behavior']['disqus_disable_mobile'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable mobile optimized version'),
      '#description' => t('When enabled, uses the <a href="http://docs.disqus.com/help/2/">disqus_disable_mobile</a> flag to tell Disqus service to never use the mobile optimized version of Disqus.'),
      '#default_value' => $disqus_config->get('behavior.disqus_disable_mobile'),
    );
    // Advanced settings.
    $form['advanced'] = array(
      '#type' => 'details',
      '#title' => t('Advanced'),
      '#group' => 'settings',
      '#description' => t('Use these settings to configure the more advanced uses of Disqus. You can find more information about these in the <a href="!applications">Applications</a> section of Disqus. To enable some of these features, you will require a <a href="!addons">Disqus Add-on Package</a>.', array(
        '!applications' => 'http://disqus.com/api/applications/',
        '!addons' => 'http://disqus.com/addons/',
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
    $form['advanced']['api'] = array(
      '#weight' => 4,
      '#type' => 'fieldset',
      '#title' => t('Disqus API Settings'),
      '#description' => t('These setting pertain to the official Disqus PHP API. You will need to install the <a href="!composer-manager">Composer Manager module</a> and run the composer-manager\'s install command to download the api files and enable api functionality. Check the <a href="!disqus">Disqus module</a> project page for more information.', array(
        '!composer-manager' => 'https://www.drupal.org/project/composer_manager',
        '!disqus' => 'https://www.drupal.org/project/disqus',
      )),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );
    if(class_exists('DisqusAPI')) {
      $form['advanced']['api']['disqus_api_update'] = array(
        '#type' => 'checkbox',
        '#title' => t('Update Threads'),
        '#description' => t('Update node titles and links via the disqus api when saving. (Requires your user access token.)'),
        '#default_value' => $disqus_config->get('advanced.api.disqus_api_update'),
        '#states' => array(
          'enabled' => array(
            'input[name="disqus_useraccesstoken"]' => array('empty' => FALSE),
          ),
        ),
      );
      $form['advanced']['api']['disqus_api_delete'] = array(
        '#type' => 'select',
        '#title' => t('Close/Remove Threads'),
        '#description' => t('Action to take when deleting a node. (Requires your user access token.)'),
        '#default_value' => $disqus_config->get('advanced.api.disqus_api_delete'),
        '#options' => array(
          DISQUS_API_NO_ACTION => t('No Action'),
          DISQUS_API_CLOSE => t('Close Thread'),
          DISQUS_API_REMOVE => t('Remove Thread'),
        ),
        '#states' => array(
          'enabled' => array(
            'input[name="disqus_useraccesstoken"]' => array('empty' => FALSE),
          ),
        ),
      );
    }
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
      '#description' => t('Provide <a href="!sso">Single Sign-On</a> access to your site.', array(
        '!sso' => 'http://disqus.com/api/sso/',
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
      '#default_value' => array($disqus_config->get('advanced.sso.disqus_logo')),
      '#upload_validators' => array(
        'file_validate_extensions' => array('gif png jpg jpeg'),
        // Disqus recommends the login button resolution as (143x32)
        // https://help.disqus.com/customer/portal/articles/236206-integrating-single-sign-on
        'file_validate_image_resolution' => array('143x32'),
      ),
      '#states' => array(
        'disabled' => array(
          'input[name="disqus_sso"]' => array('checked' => FALSE),
        ),
        'visible' => array(
          'input[name="disqus_use_site_logo"]' => array('checked' => FALSE),
        ),
      ),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('disqus.settings');
    $config
      ->set('disqus_domain', $form_state->getValue('disqus_domain'))
      ->set('behavior.disqus_localization', $form_state->getValue('disqus_localization'))
      ->set('behavior.disqus_inherit_login', $form_state->getValue('disqus_inherit_login'))
      ->set('behavior.disqus_disable_mobile', $form_state->getValue('disqus_disable_mobile'))
      ->set('advanced.disqus_useraccesstoken', $form_state->getValue('disqus_useraccesstoken'))
      ->set('advanced.disqus_publickey', $form_state->getValue('disqus_publickey'))
      ->set('advanced.disqus_secretkey', $form_state->getValue('disqus_secretkey'))
      ->set('advanced.sso.disqus_sso', $form_state->getValue('disqus_sso'))
      ->set('advanced.sso.disqus_use_site_logo', $form_state->getValue('disqus_use_site_logo'))
      ->save();

    if($form_state->hasValue('disqus_api_update')) {
      $config->set('advanced.api.disqus_api_update', $form_state->getValue('disqus_api_update'))->save();
    }

    if($form_state->hasValue('disqus_api_delete')) {
      $config->set('advanced.api.disqus_api_delete', $form_state->getValue('disqus_api_delete'))->save();
    }

    $old_logo = $config->get('advanced.sso.disqus_logo');
    $new_logo = (!$form_state->isValueEmpty('disqus_logo')) ? $form_state->getValue(array('disqus_logo', 0)) : '';

    // Ignore if the file hasn't changed.
    if ($new_logo != $old_logo) {
      // Remove the old file and usage if previously set.
      if (!empty($old_logo)) {
        $file = file_load($old_logo);
        $this->fileUsage->delete($file, 'disqus', 'disqus');
      }
      // Update the new file and usage.
      if (!empty($new_logo)) {
        $file = file_load($new_logo);
        $this->fileUsage->add($file, 'disqus', 'disqus', 1);
      }
    }
    $config->set('advanced.sso.disqus_logo', $new_logo)->save();
    parent::submitForm($form, $form_state);
  }

}
