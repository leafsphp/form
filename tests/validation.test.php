<?php

declare(strict_types=1);

use Leaf\Form;

test('can add a custom rule', function () {
  $rules = Form::supportedRules();
  $rulesCount = count($rules);

  expect($rules)->not()->toContain('customValidationRule');

  Form::rule('customValidationRule', function ($field, $value) {
    return 1 !== 2;
  });

  $rules = Form::supportedRules();
  $newRulesCount = count($rules);

  expect($rules)->toContain('customValidationRule');
  expect($newRulesCount)->toBe($rulesCount + 1);
});

test('executes a custom rule', function () {
  Form::rule('dataIsOne', function ($field, $value) {
    return '1' == $value;
  });

  expect(Form::validateField('test', '1', 'dataIsOne'))
    ->toBe(true);
  expect(Form::validateField('test', '0', 'dataIsOne'))
    ->toBe(false);
});

test('rules accept parameters', function () {
  Form::rule('customIsEqual', function ($field, $value, $params) {
    return $value == $params;
  });

  expect(Form::validateField('test', 'example', 'customIsEqual:example'))
    ->toBe(true);
  expect(Form::validateField('test', 'wrong', 'customIsEqual:example'))
    ->toBe(false);
});

test('returns a custom error message', function () {
  Form::rule('ruleWithError', function ($field, $value, $params) {
    if ($value != $params) {
      Form::addError($field, "$value is not equal to $params");
      return false;
    }

    return true;
  });

  expect(Form::validateField('test', 'wrong', 'ruleWithError:example'))
    ->toBe(false);
  expect(Form::errors())->toHaveKey('test');
  expect(Form::errors()['test'] ?? '')->toBe('wrong is not equal to example');
});
