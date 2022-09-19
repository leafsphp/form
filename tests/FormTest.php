<?php
declare(strict_types=1);

use Leaf\Form;

it("supports 11 default rules", function () {
  $rules = Form::supportedRules();
  expect($rules)->toBeArray();
  expect(count($rules))->toBe(11);
});

it("has some known default rules", function () {
  $rules = Form::supportedRules();
  expect(in_array('required', $rules))->toBe(true);
  expect(in_array('email', $rules))->toBe(true);
});

it("can add a custom rule", function () {
  $rules = Form::supportedRules();
  $rules_count = count($rules);
  expect(in_array('custom_equal_rule', $rules))->toBe(false);

  Form::rule("custom_equal_rule", function ($field, $value, $params) {
    return $value == $params;
  });

  $rules = Form::supportedRules();
  $new_rules_count = count($rules);
  expect(in_array('custom_equal_rule', $rules))->toBe(true);
  expect($new_rules_count)->toBe($rules_count + 1);
});

it("executes a custom rule", function () {
  Form::rule("custom_equal_rule", function ($field, $value, $params) {
    return $value == $params;
  });

  expect(Form::validateField("test", "example", "custom_equal_rule:example"))
    ->toBe(true);
  expect(Form::validateField("test", "wrong", "custom_equal_rule:example"))
    ->toBe(false);
});

