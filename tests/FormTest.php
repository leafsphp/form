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

it("validates the rule 'number' for correct values", function ($value) {
    expect(Form::validateField("test", $value, "number"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");
})->with([
  "0",
  "1",
  "12358",
]);

it("validates the rule 'number' for wrong values", function ($value) {
    expect(Form::validateField("test", $value, "number"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test must only contain numbers");
})->with([
  "",
  "not-a-number",
]);

it("validates the rule 'text' for correct values", function ($value) {
    expect(Form::validateField("test", $value, "text"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");
})->with([
  "Lorem ipsum",
  "ALL CAPS",
  "WORD",
  "all lower",
  "under_score",
  " ",
]);

it("validates the rule 'text' for wrong values", function ($value) {
    expect(Form::validateField("test", $value, "text"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test must only contain text and spaces");
})->with([
  "",
  "1234",
  "invalid 1234",
  "No punctuation.",
]);

it("validates the rule 'textonly' for correct values", function ($value) {
    expect(Form::validateField("test", $value, "textonly"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");
})->with([
  "Lorem",
  "CAPS",
  "lower",
  "under_score",
  " ",
]);

it("validates the rule 'textonly' for wrong values", function ($value) {
    expect(Form::validateField("test", $value, "textonly"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test must only contain text");
})->with([
  "",
  " ",
  "no space",
  "1234",
  "invalid 1234",
  "No punctuation.",
]);

it("validates the rule 'validUsername' for correct values", function ($value) {
    expect(Form::validateField("test", $value, "validusername"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");
})->with([
  "Lorem",
  "CAPS",
  "lower",
  "under_score",
  "user0012",
  "14word",
  "14_16",
  "123",
]);

it("validates the rule 'validUsername' for wrong values", function ($value) {
    expect(Form::validateField("test", $value, "validusername"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test must only contain characters 0-9, A-Z and _");
})->with([
  "",
  " ",
  "user123 ",
  "user 123",
  "No space",
  "No_punctuation.",
]);

it("validates the rule 'email' for correct values", function ($value) {
    expect(Form::validateField("test", $value, "email"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");
})->with([
  "user@example.org",
]);

it("validates the rule 'email' for wrong values", function ($value) {
    expect(Form::validateField("test", $value, "email"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test must be a valid email");
})->with([
  "",
  " ",
  "user@",
  "@domain",
  "user@domain",
]);

it("validates the rule 'nospaces' for correct values", function ($value) {
    expect(Form::validateField("test", $value, "nospaces"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");
})->with([
  "",
  "word",
  "words-and-dot.",
  "Text_without_space",
  "user@example.org",
]);

it("validates the rule 'nospaces' for wrong values", function ($value) {
    expect(Form::validateField("test", $value, "nospaces"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test can't contain any spaces");
})->with([
  " ",
  " space-in-front",
  "space-at-end ",
  "space between",
]);

it("validates the rule 'max'", function () {
    expect(Form::validateField("test", "long", "max:6"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");

    expect(Form::validateField("test", "longer", "max:6"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");

    expect(Form::validateField("test", "longest", "max:6"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test can't be more than 6 characters");
});

it("validates the rule 'min'", function () {
    expect(Form::validateField("test", "longest", "min:6"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");

    expect(Form::validateField("test", "longer", "min:6"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");

    expect(Form::validateField("test", "long", "min:6"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test can't be less than 6 characters");
});

it("validates the rule 'date' for correct values", function ($value) {
    expect(Form::validateField("test", $value, "date"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");
})->with([
  "today",
  "2022-09-22",
]);

it("validates the rule 'date' for wrong values", function ($value) {
    expect(Form::validateField("test", $value, "date"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test must be a valid date");
})->with([
  "",
  " ",
  "no date",
  "date with text 2022-09-22",
  "time: 14:39:56",
]);
