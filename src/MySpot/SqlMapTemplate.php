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
            ) = SqlMapTemplate::parse($text);
        $this->variables = $variables;
        $this->parsedNormalVariables = array_unique($normalVariables);
        $this->parsedConditionVariables = array_unique($conditionVariables);
    }

    /**
     * A simple parser implemented in a simple FSM
     * @param string $text
     * @return array
     * @throws SqlMapException
     */
    private static function parse(string $text)
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

        $endVarParsing = function ($type = self::VAR_TYPE_NORMAL) use ($text, &$vars, &$isParsingVariable, &$parsingVarName, &$parsingVarStart, &$position, &$varNames, &$conditionNames, &$subInNames) {
            if (empty($parsingVarName)) {
                throw new SqlMapException(sprintf("Unexpected variable name: '%s', position: %s, template text: '%s'", $parsingVarName, $position, $text));
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
        };

        for (; $position < strlen($text); $position++) {
            $char = $text[$position];

            switch ($char) {
                case ':':
                    if ($isParsingVariable) {
                        $endVarParsing(self::VAR_TYPE_SUB_IN);
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
                    $endVarParsing(self::VAR_TYPE_CONDITION);
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
                    $isParsingVariable and $endVarParsing();
                    if ($isParsingSubExpression) {
                        $isParsingSubExpression = false;

                        $currentVar[] = $parsingSubExpressionStart;
                        $currentVar[] = $position + 1 - $parsingSubExpressionStart;
                        unset($currentVar);
                        $parsingSubExpressionStart = -1;
                    }
                    break;
                default:
                    $isParsingVariable and $endVarParsing();
            }

        }

        if ($isParsingVariable) {
            $endVarParsing();
        }

        if (count($duplicates = array_intersect($varNames, $subInNames))) {
            throw new SqlMapException(sprintf('Duplicated variable name: %s', json_encode($duplicates)));
        }

        return ['variables' => $vars, 'normalVariables' => $varNames, 'conditionVariables' => $conditionNames, 'subInVariables' => $subInNames];
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
        $text = $this->text;
        $preparedSubReplaces = [];
        $strips = [];

        foreach ($this->variables as $item) {
            list($name, $type, $offset, $length) = $item;
            $currentParam = $params[$name] ?? [];
            $paramValue = $currentParam[0] ?? null;

            if ($type == self::VAR_TYPE_CONDITION) {
                list(4 => $subOffset, 5 => $subLength) = $item;
                if ($paramValue) {
                    // It must be replaced in reverse order, otherwise the offset will be wrong
                    // $strips[] = [$subOffset + $subLength - 1, $subOffset + $subLength];
                    $text = substr_replace($text, ' ', $subOffset + $subLength - 1, 1); // strip '}'
                    // $strips[] = [$offset, $subOffset + 1];
                    $text = substr_replace($text, str_repeat(' ', $subOffset - $offset + 1), $offset, $subOffset - $offset + 1); // strip ':variable?{', include space
                } else {
                    // Actually length: $length + $subLength + $subOffset - $offset - $length
                    // $strips[] = [$offset, $subOffset + $subLength];
                    $text = substr_replace($text, str_repeat(' ', $subLength + $subOffset - $offset), $offset, $subLength + $subOffset - $offset);
                }
            } else if ($type == self::VAR_TYPE_SUB_IN) {
                if (empty($paramValue)) {
                    array_unshift($preparedSubReplaces, ['', $offset, $length + 1]);
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
                    $generateName = 'mySpotGenerated' . ucfirst($name) . $index++;
                    $fragments[] = $generateName;
                    $params[$generateName] = [$subItem, $paramType];
                }
                unset($params[$name]);
                array_unshift($preparedSubReplaces, [sprintf('(:%s) ', implode(', :', $fragments)), $offset, $length + 1]);
            }
        }
        foreach ($preparedSubReplaces as $replace) {
            $text = substr_replace($text, ...$replace);
        }

        $text = trim($text);

        list('normalVariables' => $varNames) = self::parse($text);
        $params = array_filter($params, function ($key) use ($varNames) {
            if (in_array($key, $varNames)) {
                return true;
            }
        }, ARRAY_FILTER_USE_KEY);

        return [$text, $params];
    }

}
