<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldWidget;

use Drupal\autocomplete_deluxe\Plugin\Field\FieldWidget\AutocompleteDeluxeWidget;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\BeforeCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\KeyValueStore\KeyValueFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountInterface;
use Drupal\ezcontent_smart_article\GenerateSmartTags;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'options_buttons' widget.
 *
 * @FieldWidget(
 *   id = "ez_autocomplete_deluxe",
 *   label = @Translation("Ez Autocomplete Deluxe"),
 *   field_types = {
 *     "ezcontent_smart_tags"
 *   },
 *   multiple_values = TRUE
 * )
 */
class EzAutocompleteDeluxeWidget extends AutocompleteDeluxeWidget implements ContainerFactoryPluginInterface {

  /**
   * The renderer object.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentTextTaggingManager
   */
  protected $renderer;

  /**
   * EzContent Smart Tags.
   *
   * @var \Drupal\ezcontent_smart_article\GenerateSmartTags
   */
  protected $smartTags;

  /**
   * EzAutocompleteDeluxeWidget constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition for the operation.
   * @param array $settings
   *   The formatter settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Current account.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactory $key_value
   *   Key value storage.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer object.
   * @param \Drupal\ezcontent_smart_article\GenerateSmartTags $generate_smart_tags
   *   EzContent Smart Tags service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ModuleHandlerInterface $module_handler, AccountInterface $account, KeyValueFactory $key_value, Renderer $renderer, GenerateSmartTags $generate_smart_tags) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $module_handler, $account, $key_value);
    $this->renderer = $renderer;
    $this->smartTags = $generate_smart_tags;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('keyvalue'),
      $container->get('renderer'),
      $container->get('ezcontent_smart_article.generate_smarttags')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#prefix'] = '<div class="tags-link-field">';
    $element['#suffix'] = '</div>';
    $element['smart_tags_submit'] = [
      '#type' => 'submit',
      '#name' => 'smart_tags_submit',
      '#value' => $this->t('Generate Tags'),
      '#weight' => 1,
      '#ajax' => [
        'callback' => [$this, 'generateTagsCallback'],
        'wrapper' => 'generate-tags',
      ],
    ];
    $element['#attached']['library'][] = 'ezcontent_smart_article/ez_auto_deluxe_smart_tags';
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function afterBuild(array $element, FormStateInterface $form_state) {
    parent::afterBuild($element, $form_state);

    $class = get_class();
    $element['#element_validate'][] = [$class, 'validateNoDuplicates'];

    return $element;
  }

  /**
   * Set a form error if there are duplicate entity ids.
   */
  public static function validateNoDuplicates(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $input = NestedArray::getValue($form_state->getValues(), $element['#parents']);

    $ids = array_column($input['target_id'], 'target_id');

    // Check that there aren't duplicate entity_id values.
    if (count($ids) !== count(array_flip($ids))) {
      $form_state->setError($element, 'Field "' . $element['target_id']['#title'] . '" doesn\'t allow duplicates.');
    }

  }

  /**
   * Custom ajax callback for generating and suggesting tags.
   *
   * @param array $form
   *   The form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Returns tag suggestions.
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function generateTagsCallback(array &$form, FormStateInterface $form_state) {
    $tags = [];
    $response = new AjaxResponse();
    // Fetch the source field and its value.
    $link_field = $this->getFieldSetting('long_text_fields');
    $field = $form_state->getValue($link_field);
    // Get tags.
    if (!empty($field[0]['value'])) {
      $tags = $this->smartTags->generateTags($field[0]['value']);
    }
    // Display tag suggestions.
    if (!empty($tags)) {
      $auto_tags = [
        '#theme' => 'autocomplete_deluxe_smart_tags',
        '#tags' => $tags,
      ];
      $rendered_field = $this->renderer->render($auto_tags);
      // Add tags into the autocomplete deluxe field.
      $response->addCommand(new BeforeCommand('.autocomplete-deluxe-form.ui-autocomplete-input', $rendered_field));
      // Prepare tags for adding them into the autocomplete deluxe value field.
      $value = '';
      foreach ($tags as $tag) {
        $value = trim($value . ' ""' . $tag . '"" ');
      }
      $response->addCommand(new InvokeCommand('.autocomplete-deluxe-value-field', 'update_tags', [$value]));
    }
    return $response;
  }

}
