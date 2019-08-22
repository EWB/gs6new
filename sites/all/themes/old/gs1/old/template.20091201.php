<?php
// $Id: template.php,v 1.17.2.1 2009/02/13 06:47:44 johnalbin Exp $

/**
 * @file
 * Contains theme override functions and preprocess functions for the theme.
 *
 * ABOUT THE TEMPLATE.PHP FILE
 *
 *   The template.php file is one of the most useful files when creating or
 *   modifying Drupal themes. You can add new regions for block content, modify
 *   or override Drupal's theme functions, intercept or make additional
 *   variables available to your theme, and create custom PHP logic. For more
 *   information, please visit the Theme Developer's Guide on Drupal.org:
 *   http://drupal.org/theme-guide
 *
 * OVERRIDING THEME FUNCTIONS
 *
 *   The Drupal theme system uses special theme functions to generate HTML
 *   output automatically. Often we wish to customize this HTML output. To do
 *   this, we have to override the theme function. You have to first find the
 *   theme function that generates the output, and then "catch" it and modify it
 *   here. The easiest way to do it is to copy the original function in its
 *   entirety and paste it here, changing the prefix from theme_ to gs1_.
 *   For example:
 *
 *     original: theme_breadcrumb()
 *     theme override: gs1_breadcrumb()
 *
 *   where gs1 is the name of your sub-theme. For example, the
 *   zen_classic theme would define a zen_classic_breadcrumb() function.
 *
 *   If you would like to override any of the theme functions used in Zen core,
 *   you should first look at how Zen core implements those functions:
 *     theme_breadcrumbs()      in zen/template.php
 *     theme_menu_item_link()   in zen/template.php
 *     theme_menu_local_tasks() in zen/template.php
 *
 *   For more information, please visit the Theme Developer's Guide on
 *   Drupal.org: http://drupal.org/node/173880
 *
 * CREATE OR MODIFY VARIABLES FOR YOUR THEME
 *
 *   Each tpl.php template file has several variables which hold various pieces
 *   of content. You can modify those variables (or add new ones) before they
 *   are used in the template files by using preprocess functions.
 *
 *   This makes THEME_preprocess_HOOK() functions the most powerful functions
 *   available to themers.
 *
 *   It works by having one preprocess function for each template file or its
 *   derivatives (called template suggestions). For example:
 *     THEME_preprocess_page    alters the variables for page.tpl.php
 *     THEME_preprocess_node    alters the variables for node.tpl.php or
 *                              for node-forum.tpl.php
 *     THEME_preprocess_comment alters the variables for comment.tpl.php
 *     THEME_preprocess_block   alters the variables for block.tpl.php
 *
 *   For more information on preprocess functions and template suggestions,
 *   please visit the Theme Developer's Guide on Drupal.org:
 *   http://drupal.org/node/223440
 *   and http://drupal.org/node/190815#template-suggestions
 */

/*
 * Add any conditional stylesheets you will need for this sub-theme.
 *
 * To add stylesheets that ALWAYS need to be included, you should add them to
 * your .info file instead. Only use this section if you are including
 * stylesheets based on certain conditions.
 */
/* -- Delete this line if you want to use and modify this code
// Example: optionally add a fixed width CSS file.
if (theme_get_setting('gs1_fixed')) {
  drupal_add_css(path_to_theme() . '/layout-fixed.css', 'theme', 'all');
}
// */


/**
 * Implementation of HOOK_theme().
 */
function gs1_theme(&$existing, $type, $theme, $path) {
  $hooks = zen_theme($existing, $type, $theme, $path);
  // Add your theme hooks like this:
  /*
  $hooks['hook_name_here'] = array( // Details go here );
  */
  // @TODO: Needs detailed comments. Patches welcome!
  
return $hooks;
}

/**
 * Override or insert variables into all templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered (name of the .tpl.php file.)
 */
/* -- Delete this line if you want to use this function
function gs1_preprocess(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */
function gs1_preprocess_page(&$vars, $hook) {
  //color the band under the primary links and change the drop shadows on the primary links
  /*$colors = array( //on my server
    "menu-425" => array("#737531", ""),
    "menu-426" => array("#94866B", "tab2left.gif", "tab2aleft.gif"),
    "menu-427" => array("#5A6D52", "tab3left.gif", "tab3aleft.gif"),
    "menu-428" => array("#7B7142", "tab4left.gif", "tab4aleft.gif"),
    "menu-429" => array("#A58629", "tab5left.gif", "tab5aleft.gif"),
    "menu-430" => array("#737531", "tab6left.gif", "tab6aleft.gif"),
  );*/
  $colors = array( //on gs server
    "menu-398" => array("#737531", ""),
    "menu-399" => array("#94866B", "tab2left.gif", "tab2aleft.gif"),
    "menu-400" => array("#5A6D52", "tab3left.gif", "tab3aleft.gif"),
    "menu-401" => array("#7B7142", "tab4left.gif", "tab4aleft.gif"),
    "menu-402" => array("#A58629", "tab5left.gif", "tab5aleft.gif"),
    "menu-403" => array("#737531", "tab6left.gif", "tab6aleft.gif"),
  );
  //check to see that we're on one of the primary link pages
  foreach ($vars['primary_links'] as $index => $link) {
    $pos = strpos($index, "active-trail");
    if ($pos) {
      $index_short = substr($index, 0, $pos - 1);
      break;
    }
  }
  $front_tab = $index;
  //if so, adjust the colors and drop shadows accordingly
  if ($pos) {
    $prepos = 1;
    foreach ($vars['primary_links'] as $index => $link) {
      if ($prepos) { //haven't gotten to the active tab yet
        if ($index != $front_tab) { //haven't gotten to the active tab yet
          $vars['primary_colors'] .= '#navbar-inner li.' . $index . ' {background:no-repeat '
          . $colors[$index][0] . ' url(/sites/all/themes/gs1/grafix/' . $colors[$index][2] . ");}\n";
        }else{ //at the active tab
	  $vars['primary_colors'] .= '#primary {border-bottom: 36px solid ' . $colors[$index_short][0] . ";}\n"
	    . '#navbar-inner li.' . $index_short . ' {background:no-repeat ' . $colors[$index_short][0]
            . ' url(/sites/all/themes/gs1/grafix/' . $colors[$index_short][2] . ");}\n";
	  $prepos = 0;
        }
      }else{ //after the active tab
	$vars['primary_colors'] .= '#navbar-inner li.' . $index . ' {background:no-repeat '
        . $colors[$index][0] . ' url(/sites/all/themes/gs1/grafix/' . $colors[$index][1] . ");}\n";
      }
    }
  }else{ //otherwise, just do all drop shadows on the right
    foreach ($vars['primary_links'] as $index => $link) {
      $vars['primary_colors'] .= '#navbar-inner li.' . $index . ' {background:no-repeat '
      . $colors[$index][0] . ' url(/sites/all/themes/gs1/grafix/' . $colors[$index][1] . ");}\n";
    }
    //same color for the band below the tabs as the color of the first tab.
    foreach ($vars['primary_links'] as $index => $link) { //not very pretty way of finding the first tab
      $vars['primary_colors'] .= '#primary {border-bottom: 36px solid ' . $colors[$index][0] . ";}\n";
      break;
    }
  }
}

/**
 * Override or insert variables into the page templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("page" in this case.)
 */
/* -- Delete this line if you want to use this function
function gs1_preprocess_page(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the node templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("node" in this case.)
 */
/* -- Delete this line if you want to use this function
function gs1_preprocess_node(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the comment templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("comment" in this case.)
 */
/* -- Delete this line if you want to use this function
function gs1_preprocess_comment(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the block templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
/* -- Delete this line if you want to use this function
function gs1_preprocess_block(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */