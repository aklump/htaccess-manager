<?php

namespace AKlump\JsonSchema\Merge;

use InvalidArgumentException;

class MergeSchemas {

  public function __invoke(...$schemas) {
    $schema = array_shift($schemas);
    while ($merge_schema = array_shift($schemas)) {
      $schema = array_merge_recursive($schema, $merge_schema);
    }

    $this->recursivelyProcessValues($schema);

    return $schema;
  }

  private function recursivelyProcessValues(&$value, $context = NULL) {
    $context['depth'] = $context['depth'] ?? 0;

    if (isset($context['key'])) {
      if ($context['depth'] === 1 && in_array($context['key'], $this->getRootOnlyStringValueKeys())) {
        $this->ensureValueIsString($value);
      }
      if (in_array($context['key'], $this->getStringValueKeys())) {
        $this->ensureValueIsString($value);
      }
      if (in_array($context['key'], $this->getConcatenateValueKeys())) {
        $this->ensureValueIsStringByConcantenation($value);
      }
      if (in_array($context['key'], $this->getStringOrUniqueArrayKeys())) {
        $this->ensureValueIsString($value, FALSE);
      }
    }
    if (is_array($value)) {
      foreach ($value as $k => &$v) {
        $context['key'] = $k;
        ++$context['depth'];
        $this->recursivelyProcessValues($v, $context);
        --$context['depth'];
      }
      unset($v);
    }
  }

  private function getRootOnlyStringValueKeys(): array {
    return [
      '$id',
      '$schema',
      'title',
    ];
  }

  private function getStringValueKeys(): array {
    return [];
  }

  /**
   * @return string[] Keys that can be strings or unique arrays.
   */
  private function getStringOrUniqueArrayKeys(): array {
    return ['type'];
  }

  private function getConcatenateValueKeys(): array {
    return [
      '$comment',
      'description',
    ];
  }

  private function ensureValueIsString(&$value, bool $require_unique_merge = TRUE): void {
    if (is_string($value)) {
      return;
    }
    if (!is_array($value)) {
      throw new InvalidArgumentException('$value must be string or array');
    }
    $unique_values = array_unique($value);
    if ($require_unique_merge && count($unique_values) !== 1) {
      throw new InvalidArgumentException(sprintf('Cannot flatten non-unique array values: %s.', implode(', ', $value)));
    }
    if (count($unique_values) === 1) {
      $value = (string) array_values($value)[0];
    }
  }

  private function ensureValueIsStringByConcantenation(&$value): void {
    if (is_string($value)) {
      return;
    }
    if (!is_array($value)) {
      throw new InvalidArgumentException('$value must be string or array');
    }
    $value = implode(' ', $value);
  }
}
