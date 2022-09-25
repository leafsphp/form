<?php

declare(strict_types=1);

use Leaf\Form;

test("validates the rule 'validUsername' for correct values", function ($value) {
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

test("validates the rule 'validUsername' for wrong values", function ($value) {
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
