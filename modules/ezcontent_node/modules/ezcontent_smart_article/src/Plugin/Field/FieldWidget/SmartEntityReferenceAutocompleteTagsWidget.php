<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingManager;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;

/**
 * Plugin implementation of the 'entity_reference_autocomplete_tags' widget.
 *
 * @FieldWidget(
 *   id = "smart_entity_reference_autocomplete_tags",
 *   label = @Translation("Smart Autocomplete (Tags style)"),
 *   description = @Translation("An autocomplete text field with tagging
 *   support."), field_types = {
 *    "ezcontent_smart_image_tags",
 *   },
 *   multiple_values = TRUE
 * )
 */
class SmartEntityReferenceAutocompleteTagsWidget extends EntityReferenceAutocompleteTagsWidget {

  /**
   * The field definition.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface
   */
  protected $fieldDefinition;

  /**
   * The widget settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * A image tagging Manager object.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentImageTaggingManager
   */
  protected $imageTaggingManager;

  /**
   * A renderer object.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Any config factory object.
   * @param \Drupal\ezcontent_smart_article\EzcontentImageTaggingManager $imageTaggingManager
   *   An image taggig manager object.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   A renderer service object.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ConfigFactoryInterface $configFactory, EzcontentImageTaggingManager $imageTaggingManager, Renderer $renderer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->configFactory = $configFactory;
    $this->imageTaggingManager = $imageTaggingManager;
    $this->renderer = $renderer;
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
      $container->get('config.factory'),
      $container->get('plugin.manager.image_tagging'),
      $container->get('renderer')
    );
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
    if (count($ids) !== count(array_flip($ids))) {
      $form_state->setError($element, t("Field @field_title doesn\'t allow duplicates.", ['@field_title' => $element['target_id']['#title']]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['target_id']['#tags'] = TRUE;
    $element['target_id']['#default_value'] = $items->referencedEntities();
    $entity = $items->getEntity();
    $field_settings = $items->getFieldDefinition()->getSettings();
    $image_field_name = $field_settings['image_fields'];
    $imageTaggingActionType = $this->configFactory->get('ezcontent_smart_article.settings')
      ->get('image_tagging_action_type');
    $uuid = $entity->uuid();
    $element['#attached']['library'][] = 'ezcontent_smart_article/ezcontent_smart_article_libs';
    $element['#attached']['drupalSettings']['imageTagOption'] = $imageTaggingActionType;
    $element['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];
    // To handle form rebuild with.
    if ($form_state->getFormObject()->getFormId() == 'entity_browser_smart_image_browser_form') {
      $element['smart_tag_hidden'] = [
        "#type" => 'textfield',
        '#required' => TRUE,
        '#prefix' => '<div class="hidden" id="smart_tag_hidden-text-id">',
        '#suffix' => '</div>',
        '#weight' => 0,
      ];
    }
    // Add generate tags button.
    $element['auto_image_tags_' . $uuid] = [
      '#prefix' => '<div class="image-tag-field-wrapper" id="auto-image-tags-' . $uuid . '">',
      '#suffix' => '</div>',
      '#weight' => 0,
    ];
    $element['generate_image_tags_' . $uuid] = [
      '#type' => 'submit',
      '#name' => 'generate_image_tags_' . $uuid,
      '#value' => $this->t('Generate Image Tags'),
      '#weight' => 11,
      '#data' => $uuid,
      '#image_field_name' => $image_field_name,
      '#prefix' => '<div>',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => [
          'generate-tags-button',
          $imageTaggingActionType == 'auto' ? 'hide' : '',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'ezcontentSmartImageTagsGenerateCallback'],
        'wrapper' => 'auto-image-tags-' . $uuid,
        'effect' => 'fade',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => 'Please wait...',
        ],
      ],
    ];
    return $element;
  }

  /**
   * Image Tags generator callback.
   */
  public function ezcontentSmartImageTagsGenerateCallback(array &$form, FormStateInterface $form_state) {
    $uuid = $form_state->getTriggeringElement()['#data'];
    $image_field_name = $form_state->getTriggeringElement()['#image_field_name'];
    $media = $form_state->getValue([]);
    $field_data = $media[$uuid][$image_field_name];
    $fid = $field_data[0]['fids'][0];
    if (empty($fid) && !empty($form_state->getValue($image_field_name))) {
      $file_id = reset($form_state->getValue($image_field_name));
      $fid = $file_id['fids'][0];
    }
    if (!empty($fid)) {
      // @todo: fetch file object from form_state.
      $file = File::load($fid);
      $serviceType = $this->configFactory->get('ezcontent_smart_article.settings')
        ->get('image_tagging_service');
      $plugin = $this->imageTaggingManager->createInstance($serviceType);
      $tags = $plugin->getImageTags($file);
      if ($tags) {
        $response = new AjaxResponse();
        $auto_tags = [
          '#theme' => 'smarttag_template',
          '#tags' => $tags,
        ];
        $rendered_field = $this->renderer->render($auto_tags);
        $response->addCommand(new HtmlCommand('#auto-image-tags-' . $uuid, $rendered_field));
        $arguments = [NULL, ''];
        $response->addCommand(new InvokeCommand('#auto-image-tags-' . $uuid, "update_image_tags", $arguments));
        return $response;
      }

    }
  }

}
