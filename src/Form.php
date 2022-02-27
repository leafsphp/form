<?php

declare(strict_types=1);

namespace Leaf;

use Leaf\Http\Request;

/**
 * Leaf Forms
 * --------
 * Simple Form Validation with Leaf.
 * 
 * @since v1.0
 * @author Michael Darko <mickd22@gmail.com>
 */
class Form
{
	/**
	 * Array holding all caught errors
	 */
	protected static $errorsArray = [];

	/**
	 * Array holding all error messages
	 */
	protected static $messages = [
		'required' => '{field} is required',
		'number' => '{field} must only contain numbers',
		'text' => '{field} must only contain text and spaces',
		'textonly' => '{field} must only contain text',
		'validusername' => '{field} must only contain characters 0-9, A-Z and _',
		'email' => '{field} must be a valid email',
		'nospaces' => '{field} can\'t contain any spaces',
		'max' => '{field} $field can\'t be more than {params} characters',
		'min' => '{field} $field can\'t be less than {params} characters',
    'date' => '{field} must be a valid date',
	];

	/**
	 * Default and registered validation rules
	 */
	protected static $rules = [
		'required' => null,
		'number' => null,
		'text' => null,
		'textonly' => null,
		'validusername' => null,
		'username' => null,
		'email' => null,
		'nospaces' => null,
		'max' => null,
		'min' => null,
    'date' => null,
	];

	public static function addError(string $field, string $error)
	{
		static::$errorsArray[$field] = $error;
	}

	/**
	 * Set custom error messages for form validation
	 * 
	 * @param string|array $messages The messages or rule to overide
   * @param string $value The message to set if $messages is a string
	 */
	public static function messages($messages, ?string $value = null)
	{
		if (is_array($messages)) {
      foreach ($messages as $key => $message) {
        static::$messages[$key] = $message;
      }
    } else {
      static::$messages[$messages] = $value;
    }
	}

	/**
	 * Parse error messages
	 * 
	 * @param string $key The rule to evaluate
	 * @param string $field The name of the field to check
	 * @param string $value The value of the field to check
	 * @param string $params Params passed to the current rule
	 */
	public static function parseMessage(
		string $key,
		string $field,
		string $value,
		string $params = null
	): string {
		return str_replace(
			['{field}', '{value}', '{params}'],
			[$field, $value, $params],
			static::$messages[$key]
		);
	}

	/**
	 * Load default rules
	 */
	protected static function rules()
	{
		$rules = [
			'required' => function ($field, $value) {
				if (($value == '' || $value == null)) {
					static::$errorsArray[$field] =
						static::parseMessage('required', $field, $value);
					return false;
				}
			},
			'number' => function ($field, $value) {
				if (($value == '' || $value == null || !preg_match('/^[0-9]+$/', $value))) {
					static::$errorsArray[$field] =
						static::parseMessage('number', $field, $value);
					return false;
				}
			},
			'text' => function ($field, $value) {
				if (($value == '' || $value == null || !preg_match('/^[_a-zA-Z ]+$/', $value))) {
					static::$errorsArray[$field] =
						static::parseMessage('text', $field, $value);
					return false;
				}
			},
			'textonly' => function ($field, $value) {
				if (($value == '' || $value == null || !preg_match('/^[_a-zA-Z]+$/', $value))) {
					static::$errorsArray[$field] =
						static::parseMessage('textonly', $field, $value);
					return false;
				}
			},
			'validusername' => function ($field, $value) {
				if (($value == '' || $value == null || !preg_match('/^[_a-zA-Z0-9]+$/', $value))) {
					static::$errorsArray[$field] =
						static::parseMessage('validusername', $field, $value);
					return false;
				}
			},
			'username' => function ($field, $value) {
				if (($value == '' || $value == null || !preg_match('/^[_a-zA-Z0-9]+$/', $value))) {
					static::$errorsArray[$field] =
						static::parseMessage('validusername', $field, $value);
					return false;
				}
			},
			'email' => function ($field, $value) {
				if (($value == '' || $value == null || !!filter_var($value, 274) == false)) {
					static::$errorsArray[$field] =
						static::parseMessage('email', $field, $value);
					return false;
				}
			},
			'nospaces' => function ($field, $value) {
				if ($value !== trim($value) || strpos($value, ' ')) {
					static::$errorsArray[$field] =
						static::parseMessage('nospaces', $field, $value);
					return false;
				}
			},
			'max' => function ($field, $value, $params) {
				if (strlen($value) > $params) {
					static::$errorsArray[$field] =
						static::parseMessage('max', $field, $value, $params);
					return false;
				}
			},
			'min' => function ($field, $value, $params) {
				if (strlen($value) < $params) {
					static::$errorsArray[$field] =
						static::parseMessage('min', $field, $value, $params);
					return false;
				}
			},
			'date' => function ($field, $value) {
        if (!strtotime($value)) {
          static::$errorsArray[$field] =
            static::parseMessage('date', $field, $value);
          return false;
        }
			}
		];

		static::$rules = array_merge(static::$rules, $rules);
	}

	/**
	 * Apply a form rule
	 */
	protected static function applyRule($rule)
	{
		$rulePart = explode(':', $rule);
		$mainRule = $rulePart[0];

		$supportedRules = static::supportedRules();

		if (!in_array($mainRule, $supportedRules)) {
			trigger_error("$mainRule  is not a supported rule. Supported rules are " . json_encode($supportedRules));
		}

		$formRule = static::$rules[$mainRule];

		if (count($rulePart) > 1) {
			return [$formRule, $rulePart[1]];
		}

		return $formRule;
	}

	/**
	 * Get a list of all supported rules.
	 * This includes default and custom rules.
	 */
	public static function supportedRules(): array
	{
		return array_keys(static::$rules);
	}

	/**
	 * Define custom rules
   * 
   * @param string|array The rules or name of the rule to define
   * @param callable|null The handler for rule if $name is a string
	 */
	public static function rule($name, $handler = null)
	{
		if (is_array($name)) {
			static::$rules = array_merge(static::$rules, $name);
		} else {
			static::$rules[$name] = $handler;
		}
	}

	/**
	 * make sure that the form data is safe to work with
	 *
	 * @param string $data The data gotten from the form field
	 *
	 * @return string
	 */
	public static function sanitizeInput(string $data): string
	{
		return htmlspecialchars(stripslashes(trim($data)));
	}

	/**
	 * Validate the given request with the given rules.
	 * 
	 * @param array|string $params The rules or name of parameter to validate
   * @param array|string $rules The validation rule(s) to apply if $params is a string
	 * 
	 * @return bool
	 */
	public static function validate($params, $rules = null): bool
	{
		$fields = [];

		if (is_array($params)) {
      foreach ($params as $param => $rule) {
        $fields[] = [
          'name' => $param,
          'value' => (new Request)->get($param),
          'rule' => $rule
        ];
      }
    } else {
      $fields[] = [
        'name' => $params,
        'value' => (new Request)->get($params),
        'rule' => $rules
      ];
    }

		foreach ($fields as $field) {
			if (is_array($field['rule'])) {
				foreach ($field['rule'] as $rule) {
					$rule = strtolower($rule);
					static::validateField($field['name'], $field['value'], $rule);
				}
			} else {
				$field['rule'] = strtolower($field['rule']);
				static::validateField($field['name'], $field['value'], $field['rule']);
			}
		}

		return (count(static::$errorsArray) === 0);
	}

	/**
	 * Validate data.
	 * 
	 * @param  array  $rules The data to be validated, plus rules
	 * @param  array  $messages
	 * 
	 * @return bool
	 */
	public static function validateData(array $rules, array $messages = []): bool
	{
		$fields = [];

		foreach ($rules as $param => $rule) {
			$fields[] = ['name' => $param, 'value' => $param, 'rule' => $rule];
		}

		foreach ($fields as $field) {
			if (is_array($field['rule'])) {
				foreach ($field['rule'] as $rule) {
					$rule = strtolower($rule);
					return static::validateField($field['name'], $field['value'], $rule);
				}
			} else {
				$field['rule'] = strtolower($field['rule']);
				return static::validateField($field['name'], $field['value'], $field['rule']);
			}
		}

		return false;
	}

	/**
	 * Validate field data
	 * 
	 * @param string $fieldName The name of the field to validate
	 * @param string $fieldValue The value of the field to validate
	 * @param string $rule The rule to apply
	 */
	public static function validateField(string $fieldName, string $fieldValue, string $rule): bool
	{
		static::rules();

		$isValid = true;

		$data = static::applyRule($rule);

		if (is_array($data)) {
			$data = $data[0]($fieldName, $fieldValue, $data[1] ?? null);
		} else {
			$data = $data($fieldName, $fieldValue);
		}

		if ($data === false) {
			$isValid = false;
		}

		return $isValid;
	}

	/**
	 * Directly 'submit' a form without having to work with any mark-up
	 */
	public static function submit(string $method, string $action, array $fields)
	{
		$form_fields = '';

		foreach ($fields as $key => $value) {
			$form_fields = $form_fields . "<input type=\"hidden\" name=\"$key\" value=" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . ">";
		}

		echo "
			<form action=\"$action\" method=\"$method\" id=\"67yeg76tug216tdg267tgd21tuygu\">$form_fields</form>
			<script>document.getElementById(\"67yeg76tug216tdg267tgd21tuygu\").submit();</script>
		";
	}

	public static function isEmail($value): bool
	{
		return !!filter_var($value, 274);
	}

	/**
	 * Return the form fields+data
	 *
	 * @return string|array
	 */
	public static function body()
	{
		return (new Request)->body();
	}

	/**
	 * Return the form fields+data
	 *
	 * @return string|array
	 */
	public static function get()
	{
		return (new Request)->body();
	}

	/**
	 * Return the form errors
	 *
	 * @return array
	 */
	public static function errors(): array
	{
		return static::$errorsArray;
	}
}
