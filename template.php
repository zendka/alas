<?php
/**
 * @file
 * The primary PHP file for this theme.
 */


/**
 * Add Contact Us link before the Essay field
 */
function alas_preprocess_field(&$variables, $hook) {
  $element = $variables['element'];
  if ($element['#field_name'] == 'field_essay') {
    $variables['items'][0]['#prefix'] = '<p class="contact"><a href="/contact">' . t('Please contact us for further information') . '</a></p>';
  }
}


/**
 * Implements hook_form_alter()
 *
 */
function alas_form_alter(&$form, &$form_state, $form_id) {
  if ($form['#id'] == 'views-exposed-form-items-page') {
    _alas_unset_unused_taxonomy_options($form['field_categories_tid']['#options'],
      'item');
  }
}


/**
 * Unset <select> options that represent taxonomy terms with no nodes
 *
 * @param $options array the #options array in a <select> (see Form API)
 * @param $content_type string the machine name of the content type to scan for terms
 */
function _alas_unset_unused_taxonomy_options(&$options, $content_type) {
  $terms_referenced = _alas_get_referenced_terms($content_type);
  $parent_terms     = _alas_get_parent_terms();
  $white_list       = array_merge($terms_referenced, $parent_terms);

  foreach ($options as $key => $option) {
    if (!isset($option->option)) {
      continue;
    }

    reset($option->option);
    $term = key($option->option);
    if (!in_array($term, $white_list)) {
      unset($options[$key]);
    }
  }
}


/**
 * Get all terms referenced by nodes of a given content type
 *
 * @param $content_type string The machine name of the nodes to look for
 *
 * @return array Array with term ids
 */
function _alas_get_referenced_terms($content_type) {
  $nids = db_select('node', 'n')
    ->fields('n', array('nid'))
    ->condition('type', $content_type, '=')
    ->condition('status', 1, '=')
    ->execute()
    ->fetchCol();

  $nodes = node_load_multiple($nids);

  return _alas_get_terms_from_nodes($nodes, 'field_categories');
}


/**
 * Get all terms referenced by the given nodes
 *
 * @param $nodes
 * @param $taxonomy_field string The name of the field that references taxonomy terms
 *
 * @return array Array with term ids
 */
function _alas_get_terms_from_nodes($nodes, $taxonomy_field) {
  $tids = [];

  foreach ($nodes as $node) {
    if (empty($node->{$taxonomy_field}[LANGUAGE_NONE])) {
      continue;
    }

    $terms = $node->{$taxonomy_field}[LANGUAGE_NONE];
    foreach ($terms as $term) {
      $tids[] = $term['tid'];
    }
  }

  return array_unique($tids);
}


/**
 * Get all parent terms
 *
 * @return array Array with term ids
 */
function _alas_get_parent_terms() {
  return db_select('taxonomy_term_hierarchy', 't')
    ->fields('t', array('parent'))
    ->distinct()
    ->execute()
    ->fetchCol();
}
