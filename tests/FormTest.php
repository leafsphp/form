<?php

declare(strict_types=1);

use Leaf\Form;

it("supports 11 default rules", function () {
    expect(Form::supportedRules())
      ->toBeArray()
      ->toHaveCount(11);
});

it("has some known default rules", function () {
    expect(Form::supportedRules())
      ->toContain('required')
      ->toContain('email');
});

it("can add a custom rule", function () {
    $rules = Form::supportedRules();
    $rules_count = count($rules);
    expect($rules)->not()->toContain('custom_equal_rule');

    Form::rule("custom_equal_rule", function ($field, $value, $params) {
        return $value == $params;
    });

    $rules = Form::supportedRules();
    $new_rules_count = count($rules);
    expect($rules)->toContain('custom_equal_rule');
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

it("returns a custom error message", function () {
    Form::rule("custom_equal_rule", function ($field, $value, $params) {
        if ($value != $params) {
            Form::addError($field, "This {field} did not work. {value} is not equal to {params}");

            return false;
        }

        return true;
    });

    expect(Form::validateField("test", "wrong", "custom_equal_rule:example"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
});

it("validates the rule 'required'", function () {
    expect(Form::validateField("test", "some-value", "required"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");

    expect(Form::validateField("test", "", "required"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test is required");
});
