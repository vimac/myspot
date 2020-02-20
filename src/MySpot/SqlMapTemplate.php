<?php


namespace MySpot;


/**
 * A simple sql parser for sql variables
 */
class SqlMapTemplate
{

    /**
     * @var string
     */
    private $text;

    const VAR_TYPE_NORMAL = 0;
    const VAR_TYPE_CONDITION = 1;
    const VAR_TYPE_SUB_IN = 2;

    const GENERATED_VAR_PREFIX = 'mySpotGenerated';

    /**
     * An array stored variable information, format:
     * [[VARIABLE_NAME, VARIABLE_TYPE, OFFSET_START, VARIABLE_NAME_LENGTH, [SUB_START, SUB_LENGTH]], ...]
     * SUB_START & SUB_LENGTH are available when VARIABLE_TYPE is VAR_TYPE_CONDITION
     *
     * @var array
     */
    private $variables;

    /**
     * An array stored parsed variables' name
     *
     * @var array
     */
    private $parsedNormalVariables;

    /**
     * An array stored parsed condition variables' name
     *
     * @var array
     */
    private $parsedConditionVariables;

    /**
     * SqlMapSimpleSqlTemplate constructor.
     * @param string $text
     * @throws SqlMapException
     */
    public function __construct(string $text)
    {
        $this->text = $text;
        list(
            'variables' => $variables,
            'normalVariables' => $normalVariables,
            'conditionVariables' => $conditionVariables
            ) = $this->parse($text);
        $this->variables = $variables;
        $this->parsedNormalVariables = array_unique($normalVariables);
        $this->parsedConditionVariables = array_unique($conditionVariables);
    }

    /**
     * Get all parsed normal variable names from template
     *
     * @return array
     */
    public function getParsedNormalVariables()
    {
        return $this->parsedNormalVariables;
    }

    /**
     * Get all parsed variable definitions
     *
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Get all parsed condition variable names from template
     *
     * @return array
     */
    public function getParsedConditionVariables()
    {
        return $this->parsedConditionVariables;
    }

    /**
     * @param array $params
     * @return array
     * @throws SqlMapException
     */
    public function render(array $params): array
    {
        $strips = [];
        $inserts = [];

        foreach ($this->variables as $item) {
            list($name, $type, $offset, $length) = $item;
            $currentParam = $params[$name] ?? [];
            $paramValue = $currentParam[0] ?? null;

            if ($type == self::VAR_TYPE_CONDITION) {
                list(4 => $subOffset, 5 => $subLength) = $item;
                if ($paramValue) {
                    /** Condition == true */
                    $strips[] = [$offset, $subOffset];
                    $strips[] = [$subOffset + $subLength, $subOffset + $subLength];
                } else {
                    /** Condition == false */
                    $strips[] = [$offset, $subOffset + $subLength];
                }
            } else if ($type == self::VAR_TYPE_SUB_IN) {
                if (empty($paramValue)) {
                    $strips[] = [$offset, $offset + $length + 1];
                    continue;
                }
                if (isset($currentParam[1])) {
                    $paramType = $currentParam[1];
                } else {
                    $paramType = SqlMapConst::PARAM_STR;
                }
                if (!is_array($paramValue)) {
                    throw new SqlMapException(sprintf('Parameter %s should be an array', $name));
                }
                $fragments = [];
                $index = 0;
                foreach ($paramValue as $subItem) {
                    $generateName = self::GENERATED_VAR_PREFIX . ucfirst($name) . $index++;
                    $fragments[] = $generateName;
                    $params[$generateName] = [$subItem, $paramType];
                }
                unset($params[$name]);
                $strips[] = [$offset, $offset + $length];
                $inserts[] = [$offset, '(:' . implode(', :', $fragments) . ')'];
            }
        }

        $rawText = $this->text;
        $rawLength = strlen($rawText);
        $new = '';

        for ($i = 0; $i < $rawLength; $i++) {
            foreach ($inserts as $key => $insert) {
                list($offset, $append) = $insert;
                if ($offset === $i) {
                    $new .= $append;
                    unset($inserts[$key]);
                    break;
                }
            }
            foreach ($strips as $key => $strip) {
                list($offset, $end) = $strip;
                if ($offset === $i) {
                    $i = $end;
                    unset($strips[$key]);
                    continue 2;
                }
            }

            $new .= $rawText[$i];
        }

        $new = trim($new);

        list('normalVariables' => $varNames) = self::parse($new);
        $params = array_filter($params, function ($key) use ($varNames) {
            if (in_array($key, $varNames)) {
                return true;
            }
        }, ARRAY_FILTER_USE_KEY);

        return [$new, $params];
    }

    /**
     * A simple parser implemented in a simple FSM
     * @param string $text
     * @return array
     * @throws SqlMapException
     */
    private function parse(string $text)
    {
        $isParsingVariable = false;
        $vars = [];
        $varNames = [];
        $conditionNames = [];
        $subInNames = [];
        $parsingVarName = '';
        $parsingVarStart = -1;
        $isParsingSubExpression = false;
        $parsingSubExpressionStart = -1;
        $position = 0;
        $templateLen = strlen($text);

        for (; $position < $templateLen; $position++) {
            $char = $text[$position];

            switch ($char) {
                case ':':
                    if ($isParsingVariable) {
                        $this->endVarParsing(self::VAR_TYPE_SUB_IN, $vars, $isParsingVariable, $parsingVarName, $parsingVarStart, $position, $varNames, $conditionNames, $subInNames);
                        break;
                    }
                    $isParsingVariable = true;
                    $parsingVarStart = $position;
                    break;
                case $char >= 'A' and $char <= 'Z':
                case $char >= 'a' and $char <= 'z':
                case $char >= '0' and $char <= '9':
                case '_':
                    if ($isParsingVariable) {
                        $parsingVarName .= $char;
                    }
                    break;
                case '?':
                    if (!$isParsingVariable) {
                        throw new SqlMapException(sprintf("Unexpected char, position: %s, template text: '%s'", $position, $text));
                    }
                    $this->endVarParsing(self::VAR_TYPE_CONDITION, $vars, $isParsingVariable, $parsingVarName, $parsingVarStart, $position, $varNames, $conditionNames, $subInNames);
                    $isParsingSubExpression = true;
                    break;
                case '{':
                    if (!$isParsingSubExpression) {
                        throw new SqlMapException(sprintf("Unexpected curly brace, no condition variable defined, position: %s, template text: '%s'", $position, $text));
                    }
                    if ($parsingSubExpressionStart != -1) {
                        throw new SqlMapException(sprintf("Nesting condition not support yet, position: %s, template text: '%s'", $position, $text));
                    }
                    $index = array_key_last($vars);
                    $currentVar = &$vars[$index];
                    $parsingSubExpressionStart = $position;
                    break;
                case '}':
                    $isParsingVariable and $this->endVarParsing(self::VAR_TYPE_NORMAL, $vars, $isParsingVariable, $parsingVarName, $parsingVarStart, $position, $varNames, $conditionNames, $subInNames);
                    if ($isParsingSubExpression) {
                        $isParsingSubExpression = false;

                        $currentVar[] = $parsingSubExpressionStart;
                        $currentVar[] = $position - $parsingSubExpressionStart;
                        unset($currentVar);
                        $parsingSubExpressionStart = -1;
                    }
                    break;
                default:
                    $isParsingVariable and $this->endVarParsing(self::VAR_TYPE_NORMAL, $vars, $isParsingVariable, $parsingVarName, $parsingVarStart, $position, $varNames, $conditionNames, $subInNames);
            }

        }

        if ($isParsingVariable) {
            $this->endVarParsing(self::VAR_TYPE_NORMAL, $vars, $isParsingVariable, $parsingVarName, $parsingVarStart, $position, $varNames, $conditionNames, $subInNames);
        }

        if (count($duplicates = array_intersect($varNames, $subInNames))) {
            throw new SqlMapException(sprintf('Duplicated variable name: %s', json_encode($duplicates)));
        }

        return ['variables' => $vars, 'normalVariables' => $varNames, 'conditionVariables' => $conditionNames, 'subInVariables' => $subInNames];
    }

    private function endVarParsing($type, &$vars, &$isParsingVariable, &$parsingVarName, &$parsingVarStart, &$position, &$varNames, &$conditionNames, &$subInNames)
    {
        if (empty($parsingVarName)) {
            throw new SqlMapException(sprintf("Unexpected variable name: '%s', position: %s, text: %s", $parsingVarName, $position, $this->text));
        }
        $vars[] = [$parsingVarName, $type, $parsingVarStart, $position - $parsingVarStart];
        switch ($type) {
            case self::VAR_TYPE_CONDITION:
                $conditionNames[] = $parsingVarName;
                break;
            case self::VAR_TYPE_SUB_IN:
                $subInNames[] = $parsingVarName;
                break;
            default:
                $varNames[] = $parsingVarName;
                break;
        }
        $parsingVarName = '';
        $parsingVarStart = -1;
        $isParsingVariable = false;
    }

}
