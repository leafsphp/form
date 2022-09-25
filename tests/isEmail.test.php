<?php

declare(strict_types=1);

use Leaf\Form;

test("validates the rule 'email' for correct values", function ($value) {
  expect(Form::validateField("test", $value, "email"))
    ->toBe(true);
  expect(Form::errors())->not->toHaveKey("test");
})->with([
  "user@example.org",
]);

test("validates the rule 'email' for wrong values", function ($value) {
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
