<?php

namespace Drupal\horizontal_tabs\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Provides a render element for horizontal tabs in a form.
 *
 * Formats all child and non-child details elements whose #group is assigned
 * this element's name as horizontal tabs.
 *
 * Properties:
 * - #default_tab: The HTML ID of the rendered details element to be used as
 *   the default tab. View the source of the rendered page to determine the ID.
 *
 * Usage example:
 * @code
 * $form['information'] = array(
 *   '#type' => 'horizontal_tabs',
 *   '#default_tab' => 'edit-publication',
 * );
 *
 * $form['author'] = array(
 *   '#type' => 'details',
 *   '#title' => $this->t('Author'),
 *   '#group' => 'information',
 * );
 *
 * $form['author']['name'] = array(
 *   '#type' => 'textfield',
 *   '#title' => $this->t('Name'),
 * );
 *
 * $form['publication'] = array(
 *   '#type' => 'details',
 *   '#title' => $this->t('Publication'),
 *   '#group' => 'information',
 * );
 *
 * $form['publication']['publisher'] = array(
 *   '#type' => 'textfield',
 *   '#title' => $this->t('Publisher'),
 * );
 * @endcode
 *
 * @FormElement("horizontal_tabs")
 */
class HorizontalTabs extends Element\RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#default_tab' => '',
      '#process' => [
        [$class, 'processHorizontalTabs'],
      ],
      '#pre_render' => [
        [$class, 'preRenderHorizontalTabs'],
      ],
      '#theme_wrappers' => ['horizontal_tabs', 'form_element'],
    ];
  }

  /**
   * Prepares a horizontal_tabs element for rendering.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   horizontal tabs element.
   *
   * @return array
   *   The modified element.
   */
  public static function preRenderHorizontalTabs($element) {
    // Do not render the horizontal tabs element if it is empty.
    $group = implode('][', $element['#parents']);
    if (!Element::getVisibleChildren($element['group']['#groups'][$group])) {
      $element['#printed'] = TRUE;
    }
    return $element;
  }

  /**
   * Creates a group formatted as horizontal tabs.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   details element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function processHorizontalTabs(&$element, FormStateInterface $form_state, &$complete_form) {
    if (isset($element['#access']) && !$element['#access']) {
      return $element;
    }

    // Inject a new details as child, so that form_process_details() processes
    // this details element like any other details.
    $element['group'] = [
      '#type' => 'details',
      '#theme_wrappers' => [],
      '#parents' => $element['#parents'],
    ];

    // Add an invisible label for accessibility.
    if (!isset($element['#title'])) {
      $element['#title'] = t('Horizontal Tabs');
      $element['#title_display'] = 'invisible';
    }

    $element['#attached']['library'][] = 'horizontal_tabs/horizontal-tabs';

    // The JavaScript stores the currently selected tab in this hidden
    // field so that the active tab can be restored the next time the
    // form is rendered, e.g. on preview pages or when form validation
    // fails.
    $name = implode('__', $element['#parents']);
    if ($form_state->hasValue($name . '__active_tab')) {
      $element['#default_tab'] = $form_state->getValue($name . '__active_tab');
    }
    $element[$name . '__active_tab'] = [
      '#type' => 'hidden',
      '#default_value' => $element['#default_tab'],
      '#attributes' => ['class' => ['horizontal-tabs__active-tab']],
    ];
    // Clean up the active tab value so it's not accidentally stored in
    // settings forms.
    $form_state->addCleanValueKey($name . '__active_tab');

    return $element;
  }

}
