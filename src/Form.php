<?php

declare(strict_types=1);

namespace Leaf;

/**
 * Leaf Form
 * ----
 * Leaf's form validation library with enhanced wildcard and JSON validation support
 *
 * @version 3.1.0
 * @since 1.0.0
 */
class Form
{
    /**
     * Validation errors
     */
    protected $errors = [];

    /**
     * Validation rules
     */
    protected $rules = [
        'optional' => '/^.*$/',
        'email' => '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/',
        'alpha' => '/^[a-zA-Z\s]+$/',
        'text' => '/^[a-zA-Z\s]+$/',
        'textonly' => '/^[a-zA-Z]+$/',
        'alphanum' => '/^[a-zA-Z0-9\s]+$/',
        'alphadash' => '/^[a-zA-Z0-9-_]+$/',
        'username' => '/^[a-zA-Z0-9_]+$/',
        'number' => '/^[0-9]+$/',
        'float' => '/^[0-9]+(\.[0-9]+)$/',
        'date' => '/^\d{4}-\d{2}-\d{2}$/',
        'min' => '/^.{%s,}$/',
        'max' => '/^.{0,%s}$/',
        'between' => '/^.{%s,%s}$/',
        'match' => '/^%s$/',
        'contains' => '/%s/',
        'boolean' => '/^(true|false|1|0)$/',
        'truefalse' => '/^(true|false)$/',
        'ip' => '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',
        'ipv4' => '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',
        'ipv6' => '/^([a-fA-F0-9]{1,4}:){7}[a-fA-F0-9]{1,4}$/',
        'url' => '/^(https?|ftp):\/\/(-\.)?([^\s\/?\.#-]+\.?)+(\/[^\s]*)?$/i',
        'domain' => '/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
        'creditcard' => '/^([0-9]{4}-){3}[0-9]{4}$/',
        'phone' => '/^\+?(\d.*){3,}$/',
        'uuid' => '/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i',
        'slug' => '/^[a-z0-9]+(-[a-z0-9]+)*$/i',
        'json' => '/^[\w\s\-\{\}\[\]\"]+$/',
        'regex' => '/%s/',
    ];

    /**
     * Validation error messages
     */
    protected $messages = [
        'required' => '{Field} is required',
        'email' => '{Field} must be a valid email address',
        'alpha' => '{Field} must contain only alphabets and spaces',
        'text' => '{Field} must contain only alphabets and spaces',
        'string' => '{Field} must contain only alphabets and spaces',
        'textonly' => '{Field} must contain only alphabets',
        'alphanum' => '{Field} must contain only alphabets and numbers',
        'alphadash' => '{Field} must contain only alphabets, numbers, dashes and underscores',
        'username' => '{Field} must contain only alphabets, numbers and underscores',
        'number' => '{Field} must contain only numbers',
        'numeric' => '{Field} must be numeric',
        'float' => '{Field} must contain only floating point numbers',
        'hardfloat' => '{Field} must contain only floating point numbers',
        'date' => '{Field} must be a valid date',
        'min' => '{Field} must be at least %s characters long',
        'max' => '{Field} must not exceed %s characters',
        'between' => '{Field} must be between %s and %s characters long',
        'match' => '{Field} must match the %s field',
        'matchesvalueof' => '{Field} must match the value of %s',
        'contains' => '{Field} must contain %s',
        'boolean' => '{Field} must be a boolean',
        'truefalse' => '{Field} must be a boolean',
        'in' => '{Field} must be one of the following: %s',
        'notin' => '{Field} must not be one of the following: %s',
        'ip' => '{Field} must be a valid IP address',
        'ipv4' => '{Field} must be a valid IPv4 address',
        'ipv6' => '{Field} must be a valid IPv6 address',
        'url' => '{Field} must be a valid URL',
        'domain' => '{Field} must be a valid domain',
        'creditcard' => '{Field} must be a valid credit card number',
        'phone' => '{Field} must be a valid phone number',
        'uuid' => '{Field} must be a valid UUID',
        'slug' => '{Field} must be a valid slug',
        'json' => '{Field} must be a valid JSON string',
        'regex' => '{Field} must match the pattern %s',
        'array' => '{field} must be an array',
    ];

    public function __construct()
    {
        $this->rules['array'] = function ($value, $internalRules = null, $fieldName = null) {
            $isArray = is_array($value);

            if ($isArray) {
                foreach ($value as $valueItem) {
                    if ($internalRules) {
                        if (!$this->test($internalRules, $valueItem, $fieldName)) {
                            // we're tricking leaf into not adding the second error message by returning true here
                            // this is because we're already adding the error message in the test method
                            return true;
                        }
                    }
                }
            }

            return $isArray;
        };

        $this->rules['string'] = function ($value) {
            return is_string($value);
        };

        $this->rules['hardfloat'] = function ($value) {
            return is_float($value);
        };

        $this->rules['numeric'] = function ($value) {
            return is_numeric($value);
        };

        $this->rules['in'] = function ($value, $param) {
            return in_array($value, $param);
        };

        $this->rules['matchesvalueof'] = function ($value, $param) {
            return \Leaf\Http\Request::get($param) === $value;
        };
    }

    protected function test($rule, $valueToTest, $fieldName = 'item'): bool
    {
        $expandedErrors = false;

        if (is_string($rule)) {
            $rule = preg_match_all('/[^|<>]+(?:<[^>]+>)?/', $rule, $matches);
            $rule = $matches[0];
        }

        if (in_array('optional', $rule) && ($valueToTest === null || $valueToTest === '' || $valueToTest === [])) {
            return true;
        }

        if (in_array('expanded', $rule)) {
            $expandedErrors = true;
        }

        foreach ($rule as $currentRule) {
            $param = [];

            $currentRule = strtolower($currentRule);

            if ($currentRule === 'optional') {
                continue;
            }

            if ($currentRule === 'expanded') {
                continue;
            }

            if (preg_match('/^[a-zA-Z]+<(.*(\|.*)*)>$/', $currentRule)) {
                $ruleParts = explode('<', $currentRule);
                $ruleParams = str_replace('>', '', $ruleParts[1]);

                $currentRule = $ruleParts[0];
                $param = $ruleParams;
            }

            if (strpos($currentRule, ':') !== false && strpos($currentRule, '|') === false) {
                $ruleParts = explode(':', $currentRule);

                $currentRule = trim($ruleParts[0]);
                $param = $ruleParts[1] ? trim($ruleParts[1]) : null;
            }

            if (is_string($param) && preg_match('/\[(.*)\]/', $param, $matches) && strpos($param, '|') === false) {
                $param = explode(',', $matches[1]);
            }

            if (!isset($this->rules[$currentRule])) {
                throw new \Exception("Rule $currentRule does not exist");
            }

            $isMissing = $valueToTest === null || $valueToTest === '' || ($valueToTest === []);
            if ($isMissing) {
                if ($expandedErrors) {
                    $this->addError($fieldName, str_replace(
                        ['{field}', '{Field}', '{value}'],
                        [$fieldName, ucfirst($fieldName), is_array($valueToTest) ? json_encode($valueToTest) : $valueToTest],
                        $this->messages['required'] ?? '{Field} is invalid!'
                    ));
                } else {
                    $this->errors[$fieldName] = str_replace(
                        ['{field}', '{Field}', '{value}'],
                        [$fieldName, ucfirst($fieldName), is_array($valueToTest) ? json_encode($valueToTest) : $valueToTest],
                        $this->messages['required'] ?? '{Field} is invalid!'
                    );
                }

                return false;
            }

            if (is_callable($this->rules[$currentRule])) {
                if (!call_user_func($this->rules[$currentRule], $valueToTest, $param, $fieldName)) {
                    if (empty($param)) {
                        $param = ['Item'];
                    }

                    if (!is_array($param)) {
                        $param = [$param];
                    }

                    if ($expandedErrors) {
                        $this->addError($fieldName, sprintf(
                            str_replace(
                                ['{field}', '{Field}', '{value}'],
                                [$fieldName, ucfirst($fieldName), is_array($valueToTest) ? json_encode($valueToTest) : $valueToTest],
                                $this->messages[$currentRule] ?? '{Field} is invalid!'
                            ),
                            ...$param,
                        ));
                    } else {
                        $this->errors[$fieldName] = sprintf(
                            str_replace(
                                ['{field}', '{Field}', '{value}'],
                                [$fieldName, ucfirst($fieldName), is_array($valueToTest) ? json_encode($valueToTest) : $valueToTest],
                                $this->messages[$currentRule] ?? '{Field} is invalid!'
                            ),
                            ...$param,
                        );
                    }
                }

                continue;
            }

            if (!is_array($param)) {
                $param = [$param];
            }

            if (is_bool($valueToTest)) {
                $valueToTest = $valueToTest ? '1' : '0';
            }

            if (is_float($valueToTest)) {
                $valueToTest = json_encode($valueToTest, JSON_PRESERVE_ZERO_FRACTION);
            }

            if (
                !filter_var(
                    preg_match(sprintf($this->rules[$currentRule], ...$param), (string) $valueToTest),
                    FILTER_VALIDATE_BOOLEAN
                )
            ) {
                if ($expandedErrors) {
                    $this->addError($fieldName, sprintf(
                        str_replace(
                            ['{field}', '{Field}', '{value}'],
                            [$fieldName, ucfirst($fieldName), is_array($valueToTest) ? json_encode($valueToTest) : $valueToTest],
                            $this->messages[$currentRule] ?? '{Field} is invalid!'
                        ),
                        ...$param,
                    ));
                } else {
                    $this->errors[$fieldName] = sprintf(
                        str_replace(
                            ['{field}', '{Field}', '{value}'],
                            [$fieldName, ucfirst($fieldName), is_array($valueToTest) ? json_encode($valueToTest) : $valueToTest],
                            $this->messages[$currentRule] ?? '{Field} is invalid!'
                        ),
                        ...$param,
                    );
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate a single rule
     *
     * @param string|array $rule The rule(s) to validate against
     * @param mixed $valueToTest The value to validate
     * @param mixed $fieldName The rule parameter
     *
     * @return bool
     */
    public function validateRule($rule, $valueToTest, $fieldName = 'item'): bool
    {
        $this->errors = [];

        return $this->test($rule, $valueToTest, $fieldName);
    }

    /**
     * Validate form data with enhanced wildcard support
     *
     * @param array $dataSource The data to validate
     * @param array $validationSet The rules to validate against
     *
     * @return false|array Returns false if validation fails, otherwise returns the validated data
     */
    public function validate(array $dataSource, array $validationSet)
    {
        $this->errors = [];
        $output = [];

        foreach ($validationSet as $fieldPath => $rules) {
            if (empty($rules)) {
                $output[$fieldPath] = $this->getValue($dataSource, $fieldPath);
                continue;
            }

            // Check for wildcard in field path
            if (strpos($fieldPath, '*') !== false) {
                $result = $this->validateWildcardPath($dataSource, $fieldPath, $rules);
                if ($result === false) {
                    $output = false;
                }
            } else {
                // Normal validation for fields without wildcards
                $value = $this->getValue($dataSource, $fieldPath);

                if (!$this->test($rules, $value, $fieldPath)) {
                    $output = false;
                } elseif ($output !== false) {
                    if ($this->isOptional($rules) && $value === null) {
                        continue;
                    }
                    $output = $this->setValue($output, $fieldPath, $value);
                }
            }
        }

        return $output;
    }

    /**
     * Add custom validation rule
     * @param string $name The name of the rule
     * @param string|callable $handler The rule handler
     * @param string|null $message The error message
     */
    public function addRule(string $name, $handler, ?string $message = null)
    {
        $this->rules[strtolower($name)] = $handler;
        $this->messages[strtolower($name)] = $message ?? '%s is invalid!';
    }

    /**
     * Alias for addRule
     * @param string $name The name of the rule
     * @param string|callable $handler The rule handler
     * @param string|null $message The error message
     */
    public function rule(string $name, $handler, ?string $message = null)
    {
        $this->addRule($name, $handler, $message);
    }

    /**
     * Add validation error message
     * @param string|array $field The field to add the message to
     * @param string|null $message The error message if $field is a string
     */
    public function addErrorMessage($field, ?string $message = null)
    {
        return $this->addMessage($field, $message);
    }

    /**
     * Add validation error message
     * @param string|array $field The field to add the message to
     * @param string|null $message The error message if $field is a string
     */
    public function addMessage($field, ?string $message = null)
    {
        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $this->messages[$key] = $value;
            }

            return;
        }

        if (!$message) {
            throw new \Exception('Message cannot be empty');
        }

        $this->messages[$field] = $message;
    }

    /**
     * Directly 'submit' a form without having to work with any mark-up
     */
    public function submit(string $method, string $action, array $fields)
    {
        $form_fields = '';

        foreach ($fields as $key => $value) {
            $form_fields = $form_fields . "<input type=\"hidden\" name=\"$key\" value=" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '>';
        }

        echo "
			<form action=\"$action\" method=\"$method\" id=\"67yeg76tug216tdg267tgd21tuygu\">$form_fields</form>
			<script>document.getElementById(\"67yeg76tug216tdg267tgd21tuygu\").submit();</script>
		";
    }

    public function isEmail($value): bool
    {
        return !!filter_var($value, 274);
    }

    /**
     * Alias for addMessage
     * @param string|array $field The field to add the message to
     * @param string|null $message The error message if $field is a string
     */
    public function message($field, ?string $message = null)
    {
        $this->addMessage($field, $message);
    }

    /**
     * Add validation error with enhanced path support
     * @param string $field The field that has an error
     * @param string $error The error message
     */
    public function addError(string $field, string $error): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        if (is_array($this->errors[$field])) {
            $this->errors[$field][] = $error;
        } else {
            // Compatibility with original format
            $this->errors[$field] = $error;
        }
    }

    /**
     * Get a list of all supported rules.
     * @return array
     */
    public function supportedRules(): array
    {
        return array_keys($this->rules);
    }

    /**
     * Get validation errors
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Validate a path that contains wildcards
     */
    protected function validateWildcardPath(array $dataSource, string $fieldPath, $rules)
    {
        $parts = explode('.', $fieldPath);
        $wildcardIndex = array_search('*', $parts);

        if ($wildcardIndex === false) {
            // No wildcard found, fallback to normal validation
            $value = $this->getValue($dataSource, $fieldPath);
            return $this->test($rules, $value, $fieldPath);
        }

        // Build base path and remaining path
        $basePath = implode('.', array_slice($parts, 0, $wildcardIndex));
        $remainingPath = implode('.', array_slice($parts, $wildcardIndex + 1));

        $baseValue = $this->getValue($dataSource, $basePath);

        if (!is_array($baseValue)) {
            if ($this->isOptional($rules)) {
                return true; // Optional and not array, OK
            }
            return false; // Required but not array, error
        }

        $allValid = true;

        foreach ($baseValue as $index => $item) {
            $currentPath = $basePath . '.' . $index . ($remainingPath ? '.' . $remainingPath : '');

            if ($remainingPath) {
                // If remaining has more wildcards, recurse
                if (strpos($remainingPath, '*') !== false) {
                    $subResult = $this->validateWildcardPath([$index => $item], $index . '.' . $remainingPath, $rules);
                    if (!$subResult) {
                        $allValid = false;
                    }
                } else {
                    // Normal getValue for path without more wildcards
                    $currentValue = $this->getValue($item, $remainingPath);
                    if (!$this->test($rules, $currentValue, $currentPath)) {
                        $allValid = false;
                    }
                }
            } else {
                // No remainingPath, validate item itself
                if (!$this->test($rules, $item, $currentPath)) {
                    $allValid = false;
                }
            }
        }

        return $allValid;
    }

    /**
     * Check if a rule is optional
     */
    protected function isOptional($rules): bool
    {
        if (is_array($rules)) {
            return in_array('optional', $rules);
        }

        if (is_string($rules)) {
            return strpos($rules, 'optional') !== false;
        }

        return false;
    }

    /**
     * Get value using dot notation with fallback
     */
    protected function getValue($data, string $path)
    {
        if (class_exists('\Leaf\Anchor')) {
            return \Leaf\Anchor::deepGetDot($data, $path);
        }

        // Manual fallback for dot notation
        if (strpos($path, '.') === false) {
            return $data[$path] ?? null;
        }

        $parts = explode('.', $path);
        $current = $data;

        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return null;
            }
            $current = $current[$part];
        }

        return $current;
    }

    /**
     * Set value using dot notation with fallback
     */
    protected function setValue(array $data, string $path, $value): array
    {
        if (class_exists('\Leaf\Anchor')) {
            return \Leaf\Anchor::deepSetDot($data, $path, $value);
        }

        // Manual fallback for dot notation
        if (strpos($path, '.') === false) {
            $data[$path] = $value;
            return $data;
        }

        $parts = explode('.', $path);
        $current = &$data;

        for ($i = 0; $i < count($parts) - 1; $i++) {
            $part = $parts[$i];
            if (!isset($current[$part]) || !is_array($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }

        $current[end($parts)] = $value;
        return $data;
    }
}
