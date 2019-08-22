<?php
// $Id: luceneapi_facet.block.inc,v 1.1.2.14 2009/11/10 21:55:52 cpliakas Exp $

/**
 * @file
 * Renders the Search Lucene Facets facet block.
 *
 * @ingroup luceneapi
 */

/**
 * Counts the number results that will be returned if the facet is selected.
 *
 * @param $index
 *   A LuceneAPI_Search_Lucene_Proxy object, such as the one returned by the
 *   luceneapi_index_open() function.
 * @param $query
 *   A Zend_Search_Lucene_Search_Query object modeling the query.
 * @param $facets
 *   An array of enabled facets.
 * @return
 *   An array of items.
 * @throws LuceneAPI_Exception
 */
function luceneapi_facet_counts_get(LuceneAPI_Search_Lucene_Proxy $index, Zend_Search_Lucene_Search_Query $query, array $facets) {

  // initializes array of items
  $items  = array();
  foreach ($facets as $name => $facet) {

    // initializes array containing facet information
    $items[$name] = array(
      'title' => $facet['title'],
      'field' => $facet['field'],
      'element' => $facet['element'],
      'selected' => array(),
      'count' => array(),
    );

    // gets list of selected values from ones passed through the query string
    $passed = (array)luceneapi_facet_value_get($facet['element'], '');
    foreach ($passed as $cur_passed) {
      if ($cur_passed) {
        $items[$name]['selected'][$cur_passed] = $cur_passed;
      }
    }
  }

  // re-executes search query, $max is the highest count + 1
  $hits = luceneapi_find($index, $query, array(), $positive_keys, TRUE);
  $max = count($hits) + 1;

  // determines counts
  foreach ($hits as $hit) {
    try {
      $document = $index->getDocument($hit->id);
    }
    catch (Exception $e) {
      luceneapi_throw_error($e, WATCHDOG_ERROR, $index->getModule());
      continue;
    }
    foreach ($items as $name => $item) {
      try {
        $value = $document->getFieldValue($item['field']);
      }
      catch (Exception $e) {
        continue;
      }
      if ($value) {

        // Normalizes values to an array.  Items that have multiple values in a
        // field have a 'delimiter' in the facet definition.
        if (isset($facets[$name]['delimiter'])) {
          $values = explode($facets[$name]['delimiter'], $value);
        }
        else {
          $values = array($value);
        }

        // increments count for values
        foreach ($values as $value) {
          if (isset($items[$name]['count'][$value])) {
            $items[$name]['count'][$value]++;
          }
          else {
            $items[$name]['count'][$value] = 1;
          }
        }
      }
    }
  }

  // buids array of items, determines theme functions and finalizes count
  foreach ($items as $name => $item) {
    $items[$name]['items'] = array();
    if (empty($item['count'])) {
      unset($items[$name]);
      continue;
    }
    foreach ($item['count'] as $value => $count) {
      $options = array();
      $qstring = $_GET;

      // gets theme function, link path, and finalizes count
      if (!isset($item['selected'][$value])) {
        $theme_function = 'luceneapi_facet_link';
        if (empty($qstring[$item['element']]) || is_array($qstring[$item['element']])) {
          $qstring[$item['element']][$value] = $value;
        }
        else {
          $qstring[$item['element']] = array($value => $value);
        }
      }
      else {
        $options['attributes']['class'] = 'active';
        $items[$name]['count'][$value] = $count = $max;
        $theme_function = 'luceneapi_facet_link_selected';
        if (is_array($qstring[$item['element']])) {
          unset($qstring[$item['element']][$value]);
        }
        else {
          unset($qstring[$item['element']]);
        }
      }

      // adds query string to options
      $options['query'] = drupal_query_string_encode($qstring, array('q', 'page'));

      // adds item to array with theme function, counts
      $items[$name]['items'][$value] = array(
        'function' => $theme_function,
        'text' => $value,
        'path' => $_GET['q'],
        'options' => $options,
        'count' => $count,
      );
    }
  }

  return $items;
}

/**
 * Returns the array for hook_block('view') implementations od Search Lucene
 * Facet blocks.
 *
 * @param $module
 *   A string containing the module handling the search.
 * @return
 *   A string containing the themed facets.
 */
function luceneapi_facet_block_view($module) {

  // gets "empty search" settings for the module
  $variable = sprintf('luceneapi_facet:%s:empty', $module);
  $empty_settings = variable_get($variable, array());

  // adds rendered facets in the "block" relam to the block.
  $items = array();
  if ($module === ($executed = luceneapi_search_executed())) {

    // checks to see if results were returned
    global $pager_total_items;
    if (!empty($pager_total_items[0])) {

      // gets items, calculates facet counts, sorts by count
      $type = luceneapi_index_type_get($module);
      if ($items = luceneapi_facet_realm_render('block', $module, $type)) {
        $items = luceneapi_facet_sorted_counts_get($items);
      }
      else {
        $items = array();
      }

      // limits based on hard limit
      $variable = sprintf('luceneapi_facet:%s:hard_limit', $module);
      if ($hard_limit = variable_get($variable, 20)) {
        foreach ($items as $facet_name => $item) {
          $items[$facet_name]['items'] = array_slice(
            $items[$facet_name]['items'], 0, $hard_limit, TRUE
          );
        }
      }
    }
    elseif (!empty($empty_settings['no_results'])) {
      $items = luceneapi_facet_realm_render('block', $module, $type, TRUE);
    }
  }
  elseif (!empty($empty_settings['no_search'])) {
    $items = luceneapi_facet_realm_render('block', $module, $type, TRUE);
  }

  // if we have items, return block content
  return (!empty($items)) ? theme('luceneapi_facet_block', $items, $module) : '';
}

/**
 * Adds CSS and javascript for facets in the block realm.
 *
 * @return
 *   NULL
 */
function luceneapi_facet_css_js_add() {
  static $runonce = FALSE;
  if (!$runonce) {
    $runonce = TRUE;

    // adds module settings as javascript variables
    $settings = array('luceneapi_facet' => array());
    foreach (luceneapi_searchable_module_list() as $module => $name) {
      $settings['luceneapi_facet'][$module] = array(
        'limit' => variable_get(sprintf('luceneapi_facet:%s:limit', $module), 5),
      );
    }

    // adds CSS file, javascript file, javascript variables to page
    $path = drupal_get_path('module', 'luceneapi_facet');
    drupal_add_css($path .'/luceneapi_facet.css');
    drupal_add_js($path .'/luceneapi_facet.js');
    drupal_add_js($settings, 'setting');
  }
}
