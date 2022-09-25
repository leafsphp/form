<?php

declare(strict_types=1);

use Leaf\Form;

test("validates the rule 'number' for correct values", function ($value) {
    expect(Form::validateField("test", $value, "number"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");
})->with([
  "0",
  "1",
  "12358",
]);

test("validates the rule 'number' for wrong values", function ($value) {
    expect(Form::validateField("test", $value, "number"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test must only contain numbers");
})->with([
  "",
  "not-a-number",
]);
