<?php

/**
 * @file
 * Contains \Drupal\token\Controller\TokenAutocompleteController.
 */

namespace Drupal\token\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns autocomplete responses for tokens.
 */
class TokenAutocompleteController implements ContainerInjectionInterface {

  /**
   * Constructs a new TokenAutocompleteController.
   */
  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(

    );
  }

  /**
   * Retrieves suggestions for block category autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param string $token_type
   *   The token type.
   * @param string $filter
   *   The autocomplete filter.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing autocomplete suggestions.
   */
  public function autocomplete($token_type, $filter, Request $request) {
    $filter = substr($filter, strrpos($filter, '['));

    $matches = array();

    if (!drupal_strlen($filter)) {
      $matches["[{$token_type}:"] = 0;
    }
    else {
      $depth = max(1, substr_count($filter, ':'));
      $tree = token_build_tree($token_type, array('flat' => TRUE, 'depth' => $depth));
      foreach (array_keys($tree) as $token) {
        if (strpos($token, $filter) === 0) {
          $matches[$token] = levenshtein($token, $filter);
          if (isset($tree[$token]['children'])) {
            $token = rtrim($token, ':]') . ':';
            $matches[$token] = levenshtein($token, $filter);
          }
        }
      }
    }

    asort($matches);

    $keys = array_keys($matches);
    $matches = array_combine($keys, $keys);

    return new JsonResponse($matches);
  }

}