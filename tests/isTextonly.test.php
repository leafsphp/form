<?php

declare(strict_types=1);

use Leaf\Form;

test("validates the rule 'textonly' for correct values", function ($value) {
  expect(Form::validateField("test", $value, "textonly"))
    ->toBe(true);
  expect(Form::errors())->not->toHaveKey("test");
})->with([
  "Lorem",
  "CAPS",
  "lower",
  "under_score",
]);

test("validates the rule 'textonly' for wrong values", function ($value) {
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
