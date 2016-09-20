<?php
/**
 * @file
 * The primary PHP file for this theme.
 */


// Add Contact Us link before the Essay field
function alas_preprocess_field(&$variables, $hook) {
  $element = $variables['element'];
  if ($element['#field_name'] == 'field_essay') {
    $variables['items'][0]['#prefix'] = '<p class="contact"><a href="/contact">' . t('Please contact us for further information') . '</a></p>';
  }
}
