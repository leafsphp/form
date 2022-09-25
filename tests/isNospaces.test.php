<?php

declare(strict_types=1);

use Leaf\Form;

test("validates the rule 'nospaces' for correct values", function ($value) {
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

test("validates the rule 'nospaces' for wrong values", function ($value) {
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
